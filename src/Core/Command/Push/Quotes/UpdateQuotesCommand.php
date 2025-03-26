<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Quotes;

use com\zoho\crm\api\record\GetRecordsParam;
use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Model\Order;
use XLite\Model\Base\Surcharge;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Quotes;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\ResponseWrapper;

class UpdateQuotesCommand extends Command
{
    protected array $deletedItems = [];

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
            $response = $recordOperations->updateRecords($bodyWrapper, $headerInstance);

            $this->processUpdateResult(\Iidev\ZohoCRM\Model\ZohoQuote::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('UpdateQuotesCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getOrder(Order $order)
    {
        $zohoId = $order->getZohoQuote()->getZohoId();

        $record = new Record();

        $record->addFieldValue(Quotes::id(), $zohoId);

        $record->addFieldValue(Quotes::QuoteStage(), new Choice($this->getQuoteStage($order)));

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

        $record->addFieldValue(new Field('customerNotes'), $order->getNotes());

        $record->addFieldValue(new Field('staffNotes'), $order->getAdminNotes());

        $profileId = $order->getOrigProfile()->getZohoModel()?->getZohoId();

        if ($profileId) {
            $profile = new Record();
            $profile->setId($profileId);
            $record->addFieldValue(Quotes::ContactName(), $profile);
        }

        if ($order->getTotal() !== $order->getZohoQuote()->getTotal()) {
            $this->getExistingLineItems($zohoId);

            $orderedProducts = array_merge($this->getOrderItems($order->getItems()), $this->deletedItems);

            $record->addFieldValue(Quotes::QuotedItems(), $orderedProducts);

            $discount = $order->getSurchargeSumByType(Surcharge::TYPE_DISCOUNT);
            $record->addFieldValue(Quotes::Discount(), (double) abs($discount));

            $record->addFieldValue(Quotes::Adjustment(), (double) $this->getAdjustment($order));
        }

        return $record;
    }

    protected function getExistingLineItems(string $recordId): void
    {
        $recordOperations = new RecordOperations('Quotes');
        $headerInstance = new HeaderMap();
        $paramInstance = new ParameterMap();
        $paramInstance->add(GetRecordsParam::fields(), "Quoted_Items");

        try {
            $response = $recordOperations->getRecord($recordId, $paramInstance, $headerInstance);
            $this->processGetResult($response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('Error get existing line items', [
                'record_id' => $recordId,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function processGetResult($response)
    {
        if ($response == null) {
            return;
        }

        $responseHandler = $response->getObject();

        if (!($responseHandler instanceof ResponseWrapper)) {
            return;
        }

        $records = $responseHandler->getData();

        foreach ($records as $record) {
            if (!($record instanceof Record)) {
                return;
            }

            $orderItems = $record->getKeyValue('Quoted_Items');

            foreach ($orderItems as $orderItem) {
                $record = new Record();
                $record->addFieldValue(Products::id(), $orderItem->getKeyValue("id"));
                $record->addFieldValue(new Field('_delete'), null);

                $this->deletedItems[] = $record;
            }
        }
    }
}
