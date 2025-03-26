<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Quotes;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Model\Order;
use XLite\Core\Config;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Quotes;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;
use XLite\Model\Base\Surcharge;

class PushQuotesCommand extends Command
{

    public function __construct(
        array $entityIds
    ) {
        $this->entityIds = $entityIds;
        parent::__construct();
    }

    public function execute(): void
    {
        if (empty($this->entityIds)) {
            return;
        }

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

            $this->processCreateResult(\Iidev\ZohoCRM\Model\ZohoQuote::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushQuotesCommand Error:', [
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

        $record->addFieldValue(Quotes::QuoteStage(), new Choice($this->getQuoteStage($order)));

        $record->addFieldValue(Quotes::Subject(), "#{$order->getOrderNumber()}");
        $record->addFieldValue(new Field('entityId'), (string) $order->getOrderNumber());

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
}
