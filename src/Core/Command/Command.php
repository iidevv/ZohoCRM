<?php

namespace Iidev\ZohoCRM\Core\Command;

use Iidev\ZohoCRM\Core\SDK\SDK;
use XLite\Core\Database;
use com\zoho\crm\api\util\Choice;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\record\APIException;
use Iidev\ZohoCRM\Core\ZohoAwareInterface;
use XLite\InjectLoggerTrait;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\record\LineItemProduct;
use com\zoho\crm\api\record\Products;
use \XLite\Model\OrderItem;
use XLite\Model\Order;

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

        (new SDK())->initialize([]);
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
                    }

                    if ($actionResponse instanceof SuccessResponse) {
                        $details = $actionResponse->getDetails();
                        if (isset($details['id'])) {
                            $zohoId = $details['id'];

                            $zohoModel->setZohoId($zohoId);
                        }
                    } elseif ($actionResponse instanceof APIException) {
                        $errors = [
                            "message" => $actionResponse->getMessage() instanceof Choice ? $actionResponse->getMessage()->getValue() : $actionResponse->getMessage(),
                            "details" => $actionResponse->getDetails(),
                        ];
                        $zohoModel->setErrors(json_encode($errors));
                        $zohoModel->setSkipped(true);
                    }

                    if (($zohoModel instanceof \Iidev\ZohoCRM\Model\ZohoOrder) || ($zohoModel instanceof \Iidev\ZohoCRM\Model\ZohoQuote)) {
                        $zohoModel->setTotal($model->getTotal());
                    }

                    Database::getEM()->persist($zohoModel);
                    $index++;
                }

                Database::getEM()->flush();
            }
        }
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

                    if (($zohoModel instanceof \Iidev\ZohoCRM\Model\ZohoOrder) || ($zohoModel instanceof \Iidev\ZohoCRM\Model\ZohoQuote)) {
                        $zohoModel->setTotal($model->getTotal());
                    }

                    Database::getEM()->persist($zohoModel);
                    $index++;
                }

                Database::getEM()->flush();
            }
        }
    }

    protected function getZohoModel($model, $modelClass)
    {
        switch ($modelClass) {
            case \Iidev\ZohoCRM\Model\ZohoOrder::class:
                return $model->getZohoOrder();

            case \Iidev\ZohoCRM\Model\ZohoQuote::class:
                return $model->getZohoQuote();

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

        $record->addFieldValue(new Field('List_Price'), (double) $orderItem->getTotal() / $orderItem->getAmount());
        $record->addFieldValue(new Field('Quantity'), (double) $orderItem->getAmount());

        return $record;
    }

    protected function getQuoteStage(Order $order)
    {
        $paymentStatus = $order->getPaymentStatus()?->getCode();

        if ($paymentStatus === \XLite\Model\Order\Status\Payment::STATUS_QUEUED || $paymentStatus === \XLite\Model\Order\Status\Payment::STATUS_AUTHORIZED) {
            return static::QUOTE_ON_HOLD;
        }

        if ($paymentStatus === \XLite\Model\Order\Status\Payment::STATUS_PAID || $paymentStatus === \XLite\Model\Order\Status\Payment::STATUS_PART_PAID) {
            return static::QUOTE_CLOSED_WON;
        }

        return static::QUOTE_CLOSED_LOST;
    }
}