<?php

namespace Iidev\ZohoCRM\Core\Command\Push;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Core\Database;
use XLite\Model\Order;
use XLite\Core\Config;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Purchase_Orders;
use com\zoho\crm\api\record\LineItemProduct;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\record\APIException;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;
use XLite\Model\Base\Surcharge;
use \XLite\Model\OrderItem;
use XLite\InjectLoggerTrait;

class PushOrdersCommand extends Command
{
    use InjectLoggerTrait;

    private array $entityIds;
    private array $entities = [];

    public function __construct(
        array $entityIds
    ) {
        parent::__construct();
        $this->entityIds = $entityIds;
    }

    public function execute(): void
    {
        try {
            $recordOperations = new RecordOperations('Purchase_Orders');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getOrders();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);

            $this->processResult($response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushOrdersCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function processResult($response)
    {
        if ($response != null) {
            $actionHandler = $response->getObject();
            if ($actionHandler instanceof ActionWrapper) {
                $actionResponses = $actionHandler->getData();

                $index = 0;
                foreach ($actionResponses as $actionResponse) {

                    $order = $this->entities[$index];

                    if ($actionResponse instanceof SuccessResponse) {
                        $details = $actionResponse->getDetails();
                        if (isset($details['id'])) {
                            $zohoId = $details['id'];

                            $order->setZohoId($zohoId);
                        }
                    } else if ($actionResponse instanceof APIException) {
                        $this->getLogger('ZohoCRM')->error('APIException:', [
                            $order->getId(),
                            $actionResponse->getDetails(),
                        ]);
                    }
                    $index++;
                }

                Database::getEM()->flush();
            }
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

    protected function getOrder(Order $order)
    {
        $record = new Record();

        $record->addFieldValue(Purchase_Orders::PONumber(), $order->getOrderNumber());

        $date = new \DateTime('@' . $order->getDate());
        $record->addFieldValue(Purchase_Orders::PODate(), $date);

        $record->addFieldValue(Purchase_Orders::Status(), new Choice($order->getShippingStatus()->getName()));
        $record->addFieldValue(new Field('paymentStatus'), new Choice($order->getPaymentStatus()->getName()));

        $record->addFieldValue(Purchase_Orders::Subject(), $order->getOrderNumber());
        $record->addFieldValue(Purchase_Orders::PurchaseItems(), $this->getOrderItems($order->getItems()));

        $shippingAddress = $order->getProfile()->getShippingAddress();
        $billingAddress = $order->getProfile()->getBillingAddress();

        $shippingStreet = implode(' ', [$shippingAddress->getStreet(), $shippingAddress->getStreet2()]);
        $record->addFieldValue(Purchase_Orders::ShippingStreet(), $shippingStreet);
        $record->addFieldValue(Purchase_Orders::ShippingCity(), $shippingAddress->getCity());
        $record->addFieldValue(Purchase_Orders::ShippingCountry(), $shippingAddress->getCountryName());
        $record->addFieldValue(Purchase_Orders::ShippingState(), $shippingAddress->getStateName());
        $record->addFieldValue(Purchase_Orders::ShippingCode(), $shippingAddress->getZipcode());

        $billingStreet = implode(' ', [$billingAddress->getStreet(), $billingAddress->getStreet2()]);
        $record->addFieldValue(Purchase_Orders::BillingStreet(), $billingStreet);
        $record->addFieldValue(Purchase_Orders::BillingCity(), $billingAddress->getCity());
        $record->addFieldValue(Purchase_Orders::BillingCountry(), $billingAddress->getCountryName());
        $record->addFieldValue(Purchase_Orders::BillingState(), $billingAddress->getStateName());
        $record->addFieldValue(Purchase_Orders::BillingCode(), $billingAddress->getZipcode());

        $discount = $order->getSurchargeSumByType(Surcharge::TYPE_DISCOUNT);
        $record->addFieldValue(Purchase_Orders::Discount(), (double) abs($discount));

        $tax = $order->getSurchargeSumByType(Surcharge::TYPE_TAX);
        $shipping = $order->getSurchargeSumByType(Surcharge::TYPE_SHIPPING);
        $adjustment = $shipping + $tax;

        $record->addFieldValue(Purchase_Orders::Adjustment(), (double) $adjustment);

        $record->addFieldValue(Purchase_Orders::Description(), $order->getNotes());

        $record->addFieldValue(new Field('staffNotes'), $order->getAdminNotes());

        $profileId = $order->getOrigProfile()->getZohoId();

        if ($profileId) {
            $profile = new Record();
            $profile->setId($profileId);
            $record->addFieldValue(Purchase_Orders::ContactName(), $profile);
        }

        $owner = new MinifiedUser();
        $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

        $record->addFieldValue(Purchase_Orders::Owner(), $owner);

        $vendor = new Record();
        $vendor->setId(Config::getInstance()->Iidev->ZohoCRM->vendor_id);

        $record->addFieldValue(Purchase_Orders::VendorName(), $vendor);

        return $record;
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

        $lineItemProduct = new LineItemProduct();
        $lineItemProduct->setId($item->getZohoId());
        $lineItemProduct->setName($orderItem->getName());
        $record = new Record();
        $record->addFieldValue(Products::ProductName(), $lineItemProduct);

        $record->addFieldValue(new Field('List_Price'), (double) $orderItem->getTotal() / $orderItem->getAmount());
        $record->addFieldValue(new Field('Quantity'), (double) $orderItem->getAmount());

        return $record;
    }
}
