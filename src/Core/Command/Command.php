<?php

namespace Iidev\ZohoCRM\Core\Command;

use Iidev\ZohoCRM\Core\SDK\SDK;
use XLite\Core\Database;
use com\zoho\crm\api\util\Choice;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\record\APIException;
use XC\ProductVariants\Model\ProductVariant;
use XLite\InjectLoggerTrait;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\record\LineItemProduct;
use com\zoho\crm\api\record\Products;
use \XLite\Model\OrderItem;
use XLite\Model\Order;
use XLite\Model\Base\Surcharge;

class Command implements ICommand
{
    use InjectLoggerTrait;

    public const QUOTE_CLOSED_WON = 'Closed Won';
    public const QUOTE_CLOSED_LOST = 'Closed Lost';
    public const QUOTE_ON_HOLD = 'On Hold';
    protected array $entityIds;
    protected array $entities = [];

    public function __construct()
    {
        $main = new \Iidev\ZohoCRM\Main();

        if (!$main->isInitialized()) {
            throw new \Exception("Zoho SDK is not initialized. Token store is missing or invalid.");
        }

        if (!empty($this->entityIds)) {
            (new SDK())->initialize([]);
        }
    }

    public function execute(): void
    {
        // Implement as needed
    }

    protected function processCreateResult($modelClass, $response)
    {
        if ($response != null) {
            $actionHandler = $response->getObject();
            if ($actionHandler instanceof ActionWrapper) {
                $actionResponses = $actionHandler->getData();

                $index = 0;
                foreach ($actionResponses as $actionResponse) {
                    $model = $this->entities[$index];

                    $zohoModel = $this->getZohoModel($model, $modelClass);

                    if (!$zohoModel) {
                        $zohoModel = new $modelClass();
                        $zohoModel->setId($model);
                        $model->setZohoModel($zohoModel);
                    } else {
                        $zohoModel->setErrors("");
                        $zohoModel->setSkipped(false);
                    }

                    if ($actionResponse instanceof SuccessResponse) {
                        $details = $actionResponse->getDetails();
                        if (isset($details['id'])) {
                            $zohoId = $details['id'];

                            $zohoModel->setZohoId($zohoId);
                        }
                    } elseif ($actionResponse instanceof APIException) {
                        $this->handleCreateError($zohoModel, $actionResponse);
                    }

                    $zohoModel->setTotal($model->getTotal());

                    Database::getEM()->persist($zohoModel);
                    $index++;
                }

                Database::getEM()->flush();
            } else {
                $this->getLogger('ZohoCRM')->error('processCreateResult. API response may have failed', [
                    'response' => json_encode($response),
                    'modelClass' => $modelClass,
                    'actionHandler' => $actionHandler
                ]);
            }
        }
    }

    protected function handleCreateError($zohoModel, $actionResponse)
    {
        $details = $actionResponse->getDetails();

        if (isset($details['duplicate_record']) && !empty($details['duplicate_record']['id'])) {
            $zohoModel->setZohoId($details['duplicate_record']['id']);
            return;
        }

        $errors = [
            "message" => $actionResponse->getMessage() instanceof Choice ? $actionResponse->getMessage()->getValue() : $actionResponse->getMessage(),
            "details" => $actionResponse->getDetails(),
        ];
        $zohoModel->setErrors(json_encode($errors));
        $zohoModel->setSkipped(true);
    }

    protected function processUpdateResult($modelClass, $response)
    {
        if ($response != null) {
            $actionHandler = $response->getObject();
            if ($actionHandler instanceof ActionWrapper) {
                $actionResponses = $actionHandler->getData();

                $index = 0;
                foreach ($actionResponses as $actionResponse) {
                    $model = $this->entities[$index];

                    $zohoModel = $this->getZohoModel($model, $modelClass);

                    if ($actionResponse instanceof SuccessResponse) {
                        $zohoModel->setSynced(true);
                        $zohoModel->setErrors("");

                    } elseif ($actionResponse instanceof APIException) {
                        $errors = [
                            "message" => $actionResponse->getMessage() instanceof Choice ? $actionResponse->getMessage()->getValue() : $actionResponse->getMessage(),
                            "details" => $actionResponse->getDetails(),
                        ];
                        $zohoModel->setErrors(json_encode($errors));
                        $zohoModel->setSkipped(true);
                    }

                    $zohoModel->setTotal($model->getTotal());

                    Database::getEM()->persist($zohoModel);
                    $index++;
                }

                Database::getEM()->flush();
            } else {
                $this->getLogger('ZohoCRM')->error('processUpdateResult. API response may have failed', [
                    'response' => json_encode($response),
                    'modelClass' => $modelClass,
                    'actionHandler' => $actionHandler
                ]);
            }
        }
    }

    protected function getZohoModel($model, $modelClass)
    {
        switch ($modelClass) {
            case \Iidev\ZohoCRM\Model\ZohoQuote::class:
                return $model->getZohoQuote();

            case \Iidev\ZohoCRM\Model\ZohoDeal::class:
                return $model->getZohoDeal();

            default:
                return $model->getZohoModel();
        }
    }

    protected function getOrders()
    {
        $records = [];
        $orders = Database::getRepo(Order::class)->findByIds($this->entityIds);

        foreach ($orders as $order) {
            $records[] = $this->getOrder($order);
            $this->entities[] = $order;
        }

        return $records;
    }

    protected function getOrderItems($orderItems)
    {
        $items = [];

        foreach ($orderItems as $orderItem) {
            $items[] = $this->getOrderItem($orderItem);
        }

        return $items;
    }

    protected function getOrderItem(OrderItem $orderItem)
    {
        $item = $orderItem->getVariant() ? $orderItem->getVariant() : $orderItem->getProduct();

        if ($orderItem->isDeleted() || !$item->getZohoModel()?->getZohoId()) {
            $main = new \Iidev\ZohoCRM\Main();
            $item = $main->getDeletedProductPlaceholder();
        }

        $lineItemProduct = new LineItemProduct();
        $lineItemProduct->setId($item->getZohoModel()?->getZohoId());
        $lineItemProduct->setName($orderItem->getName());

        $record = new Record();
        $record->addFieldValue(Products::ProductName(), $lineItemProduct);

        $listPrice = bcdiv((string) $orderItem->getTotal(), (string) $orderItem->getAmount(), 2);

        $record->addFieldValue(new Field('List_Price'), (double) $listPrice);
        $record->addFieldValue(new Field('Quantity'), (double) $orderItem->getAmount());

        return $record;
    }

    protected function getAdjustment(Order $order)
    {
        $tax = $order->getSurchargeSumByType(Surcharge::TYPE_TAX);
        $shipping = $order->getSurchargeSumByType(Surcharge::TYPE_SHIPPING);
        $adjustment = bcadd((string) $tax, (string) $shipping, 2);

        return (double) $adjustment;
    }

    protected function getNotes($notes)
    {
        if ($notes) {
            $maxLength = 2000;
            $readMore = '... read more on the website';
            $readMoreLength = strlen($readMore);
            if (strlen($notes) >= $maxLength) {
                $trimmedNotes = substr($notes, 0, $maxLength - $readMoreLength);
                return $trimmedNotes . $readMore;
            }
            return $notes;
        }
        return '';
    }

    protected function getShippingAddress($record, $recordClass, $shippingAddress)
    {
        if (empty($shippingAddress))
            return $record;

        $shippingStreet = implode(' ', [$shippingAddress->getStreet(), $shippingAddress->getStreet2()]);
        $record->addFieldValue($recordClass::ShippingStreet(), $shippingStreet);
        $record->addFieldValue($recordClass::ShippingCity(), $shippingAddress->getCity());
        $record->addFieldValue($recordClass::ShippingCountry(), $shippingAddress->getCountryName());
        $record->addFieldValue($recordClass::ShippingState(), $shippingAddress->getStateName());
        $record->addFieldValue($recordClass::ShippingCode(), $shippingAddress->getZipcode());
        $record->addFieldValue(new Field('shippingPhone'), $shippingAddress->getPhone());

        return $record;
    }

    protected function getBillingAddress($record, $recordClass, $billingAddress)
    {
        if (empty($billingAddress))
            return $record;

        $billingStreet = implode(' ', [$billingAddress->getStreet(), $billingAddress->getStreet2()]);
        $record->addFieldValue($recordClass::BillingStreet(), $billingStreet);
        $record->addFieldValue($recordClass::BillingCity(), $billingAddress->getCity());
        $record->addFieldValue($recordClass::BillingCountry(), $billingAddress->getCountryName());
        $record->addFieldValue($recordClass::BillingState(), $billingAddress->getStateName());
        $record->addFieldValue($recordClass::BillingCode(), $billingAddress->getZipcode());
        $record->addFieldValue(new Field('billingPhone'), $billingAddress->getPhone());

        return $record;
    }

    protected function getQuoteStage(Order $order)
    {
        $shippingStatusId = $order->getShippingStatus()?->getId();

        $closedWon = [
            4,
            10,
            11,
            12,
            2,
            3,
            14,
            23,
            17,
            22,
            16,
            18,
            7,
            13,
            15,
            8
        ];

        $closedLost = [
            20,
            5,
            6,
            19,
            9
        ];

        if (in_array($shippingStatusId, $closedWon)) {
            return static::QUOTE_CLOSED_WON;
        }

        if (in_array($shippingStatusId, $closedLost)) {
            return static::QUOTE_CLOSED_LOST;
        }

        return static::QUOTE_ON_HOLD;
    }

    protected function getVariantTitle(ProductVariant $model)
    {
        $attrsString = array_reduce($model->getValues(), static function ($str, $attr) {
            $str .= $attr->asString() . ' ';
            return $str;
        }, '');

        return $model->getProduct()->getName() . ' ' . trim($attrsString);
    }

    protected function getCarts()
    {
        $records = [];
        $orders = Database::getRepo(\XLite\Model\Cart::class)->findByIds($this->entityIds);

        foreach ($orders as $order) {
            $records[] = $this->getCart($order);
            $this->entities[] = $order;
        }

        return $records;
    }

    protected function getItemsDescription($items)
    {
        if (empty($items)) {
            return 'Empty abandoned cart';
        }

        $description = "Items:\n\n";

        foreach ($items as $index => $item) {
            $lines = [];

            $itemSku = $item->getSku();
            $itemName = $item->getName();

            $quantity = $item->getAmount();

            $deletedText = !$item->getProduct()->isPersistent()
                ? ' (deleted)'
                : '';

            $itemNumber = $index + 1;
            $lines[] = "{$itemNumber}. {$itemName} ({$itemSku}) x{$quantity}{$deletedText}";

            if ($item->hasAttributeValues()) {
                foreach ($item->getAttributeValues() as $av) {
                    $lines[] = "  {$av->getName()}: {$av->getValue()}";
                }
            }

            $lines[] = "\n";

            $description .= implode("\n", $lines) . "\n";
        }

        return rtrim($description, "\n");
    }
}