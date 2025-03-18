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
use com\zoho\crm\api\record\Quotes;
use com\zoho\crm\api\record\LineItemProduct;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;
use XLite\Model\Base\Surcharge;
use \XLite\Model\OrderItem;

class PushQuotesCommand extends Command
{
    public const STATUS_CLOSED_WON = 'Closed Won';
    public const STATUS_CLOSED_LOST = 'Closed Lost';
    public const STATUS_ON_HOLD = 'On Hold';

    public function __construct(
        array $entityIds
    ) {
        parent::__construct();
        $this->entityIds = $entityIds;
    }

    public function execute(): void
    {
        try {
            $recordOperations = new RecordOperations('Quotes');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getOrders();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);

            $this->processCreateResult(\Iidev\ZohoCRM\Model\ZohoOrder::class, $response, 'quote');
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushQuotesCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
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

        $date = new \DateTime('@' . $order->getDate());
        $record->addFieldValue(new Field('placedOn'), $date);

        $record->addFieldValue(Quotes::QuoteStage(), new Choice($this->getQuoteStage($order)));

        $record->addFieldValue(Quotes::Subject(), "#{$order->getOrderNumber()}");
        $record->addFieldValue(Quotes::QuotedItems(), $this->getOrderItems($order->getItems()));

        $shippingAddress = $order->getProfile()->getShippingAddress();
        $billingAddress = $order->getProfile()->getBillingAddress();

        $shippingStreet = implode(' ', [$shippingAddress->getStreet(), $shippingAddress->getStreet2()]);
        $record->addFieldValue(Quotes::ShippingStreet(), $shippingStreet);
        $record->addFieldValue(Quotes::ShippingCity(), $shippingAddress->getCity());
        $record->addFieldValue(Quotes::ShippingCountry(), $shippingAddress->getCountryName());
        $record->addFieldValue(Quotes::ShippingState(), $shippingAddress->getStateName());
        $record->addFieldValue(Quotes::ShippingCode(), $shippingAddress->getZipcode());
        $record->addFieldValue(new Field('shippingPhone'), $shippingAddress->getPhone());

        $billingStreet = implode(' ', [$billingAddress->getStreet(), $billingAddress->getStreet2()]);
        $record->addFieldValue(Quotes::BillingStreet(), $billingStreet);
        $record->addFieldValue(Quotes::BillingCity(), $billingAddress->getCity());
        $record->addFieldValue(Quotes::BillingCountry(), $billingAddress->getCountryName());
        $record->addFieldValue(Quotes::BillingState(), $billingAddress->getStateName());
        $record->addFieldValue(Quotes::BillingCode(), $billingAddress->getZipcode());
        $record->addFieldValue(new Field('billingPhone'), $billingAddress->getPhone());

        $discount = $order->getSurchargeSumByType(Surcharge::TYPE_DISCOUNT);
        $record->addFieldValue(Quotes::Discount(), (double) abs($discount));

        $tax = $order->getSurchargeSumByType(Surcharge::TYPE_TAX);
        $shipping = $order->getSurchargeSumByType(Surcharge::TYPE_SHIPPING);
        $adjustment = $shipping + $tax;

        $record->addFieldValue(Quotes::Adjustment(), (double) $adjustment);

        $record->addFieldValue(new Field('customerNotes'), $order->getNotes());

        $record->addFieldValue(new Field('staffNotes'), $order->getAdminNotes());

        $profileId = $order->getOrigProfile()->getZohoModel()?->getZohoId();

        if ($profileId) {
            $profile = new Record();
            $profile->setId($profileId);
            $record->addFieldValue(Quotes::ContactName(), $profile);
        }

        $owner = new MinifiedUser();
        $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

        $record->addFieldValue(Quotes::Owner(), $owner);

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

        if ($orderItem->isDeleted()) {
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
            return static::STATUS_ON_HOLD;
        }

        if ($paymentStatus === \XLite\Model\Order\Status\Payment::STATUS_PAID || $paymentStatus === \XLite\Model\Order\Status\Payment::STATUS_PART_PAID) {
            return static::STATUS_CLOSED_WON;
        }

        return static::STATUS_CLOSED_LOST;
    }
}
