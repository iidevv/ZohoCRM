<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Orders;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Model\Order;
use XLite\Core\Config;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Sales_Orders;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;
use XLite\Model\Base\Surcharge;

class PushOrdersCommand extends Command
{
    public function __construct(
        array $entityIds
    ) {
        parent::__construct();
        $this->entityIds = $entityIds;
    }

    public function execute(): void
    {
        try {
            $recordOperations = new RecordOperations('Sales_Orders');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getOrders();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);

            $this->processCreateResult(\Iidev\ZohoCRM\Model\ZohoOrder::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushOrdersCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getOrder(Order $order)
    {
        $record = new Record();

        $date = new \DateTime('@' . $order->getDate());
        $record->addFieldValue(new Field('placedOn'), $date);

        $record->addFieldValue(Sales_Orders::Status(), new Choice($order->getShippingStatus()->getName()));
        $record->addFieldValue(new Field('paymentStatus'), new Choice($order->getPaymentStatus()->getName()));

        $record->addFieldValue(Sales_Orders::Subject(), "#{$order->getOrderNumber()}");
        $record->addFieldValue(Sales_Orders::OrderedItems(), $this->getOrderItems($order->getItems()));

        $shippingAddress = $order->getProfile()->getShippingAddress();
        $billingAddress = $order->getProfile()->getBillingAddress();

        $shippingStreet = implode(' ', [$shippingAddress->getStreet(), $shippingAddress->getStreet2()]);
        $record->addFieldValue(Sales_Orders::ShippingStreet(), $shippingStreet);
        $record->addFieldValue(Sales_Orders::ShippingCity(), $shippingAddress->getCity());
        $record->addFieldValue(Sales_Orders::ShippingCountry(), $shippingAddress->getCountryName());
        $record->addFieldValue(Sales_Orders::ShippingState(), $shippingAddress->getStateName());
        $record->addFieldValue(Sales_Orders::ShippingCode(), $shippingAddress->getZipcode());
        $record->addFieldValue(new Field('shippingPhone'), $shippingAddress->getPhone());

        $billingStreet = implode(' ', [$billingAddress->getStreet(), $billingAddress->getStreet2()]);
        $record->addFieldValue(Sales_Orders::BillingStreet(), $billingStreet);
        $record->addFieldValue(Sales_Orders::BillingCity(), $billingAddress->getCity());
        $record->addFieldValue(Sales_Orders::BillingCountry(), $billingAddress->getCountryName());
        $record->addFieldValue(Sales_Orders::BillingState(), $billingAddress->getStateName());
        $record->addFieldValue(Sales_Orders::BillingCode(), $billingAddress->getZipcode());
        $record->addFieldValue(new Field('billingPhone'), $billingAddress->getPhone());

        $discount = $order->getSurchargeSumByType(Surcharge::TYPE_DISCOUNT);
        $record->addFieldValue(Sales_Orders::Discount(), (double) abs($discount));

        $tax = $order->getSurchargeSumByType(Surcharge::TYPE_TAX);
        $shipping = $order->getSurchargeSumByType(Surcharge::TYPE_SHIPPING);
        $adjustment = $shipping + $tax;

        $record->addFieldValue(Sales_Orders::Adjustment(), (double) $adjustment);

        $record->addFieldValue(new Field('customerNotes'), $order->getNotes());

        $record->addFieldValue(new Field('staffNotes'), $order->getAdminNotes());

        $profileId = $order->getOrigProfile()->getZohoModel()?->getZohoId();

        if ($profileId) {
            $profile = new Record();
            $profile->setId($profileId);
            $record->addFieldValue(new Field('contactName'), $profile);
        }

        $quoteId = $order->getZohoQuote()?->getZohoId();

        if ($quoteId) {
            $quote = new Record();
            $quote->setId($quoteId);
            $record->addFieldValue(Sales_Orders::QuoteName(), $quote);
        }

        $owner = new MinifiedUser();
        $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

        $record->addFieldValue(Sales_Orders::Owner(), $owner);

        return $record;
    }
}
