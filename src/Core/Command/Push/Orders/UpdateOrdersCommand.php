<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Orders;

use com\zoho\crm\api\record\GetRecordsParam;
use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Model\Order;
use XLite\Model\Base\Surcharge;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Sales_Orders;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\ResponseWrapper;

class UpdateOrdersCommand extends Command
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
            $recordOperations = new RecordOperations('Sales_Orders');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getOrders();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->updateRecords($bodyWrapper, $headerInstance);

            $this->processUpdateResult(\Iidev\ZohoCRM\Model\ZohoOrder::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('UpdateOrdersCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getOrder(Order $order)
    {
        $zohoId = $order->getZohoModel()?->getZohoId();

        $record = new Record();

        $record->addFieldValue(Sales_Orders::id(), $zohoId);

        $record->addFieldValue(Sales_Orders::Status(), new Choice($order->getShippingStatus()->getName()));
        $record->addFieldValue(new Field('paymentStatus'), new Choice($order->getPaymentStatus()->getName()));

        $shippingAddress = $order->getProfile()?->getShippingAddress();
        $billingAddress = $order->getProfile()?->getBillingAddress();

        $record = $this->getShippingAddress($record, Sales_Orders::class, $shippingAddress);
        $record = $this->getBillingAddress($record, Sales_Orders::class, $billingAddress);

        $record->addFieldValue(new Field('customerNotes'), $this->getNotes($order->getNotes()));

        $record->addFieldValue(new Field('staffNotes'), $this->getNotes($order->getAdminNotes()));

        $profileId = $order->getOrigProfile()?->getZohoModel()?->getZohoId();

        if ($profileId) {
            $profile = new Record();
            $profile->setId($profileId);
            $record->addFieldValue(Sales_Orders::ContactName(), $profile);
        }

        if ($order->getTotal() !== $order->getZohoModel()->getTotal()) {
            $this->getExistingLineItems($zohoId);

            $orderedProducts = array_merge($this->getOrderItems($order->getItems()), $this->deletedItems);

            $record->addFieldValue(Sales_Orders::OrderedItems(), $orderedProducts);

            $discount = $order->getSurchargeSumByType(Surcharge::TYPE_DISCOUNT);
            $record->addFieldValue(Sales_Orders::Discount(), (double) abs($discount));

            $record->addFieldValue(Sales_Orders::Adjustment(), $this->getAdjustment($order));
        }

        return $record;
    }

    protected function getExistingLineItems(string $recordId): void
    {
        $recordOperations = new RecordOperations('Sales_Orders');
        $headerInstance = new HeaderMap();
        $paramInstance = new ParameterMap();
        $paramInstance->add(GetRecordsParam::fields(), "Ordered_Items");

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

            $orderItems = $record->getKeyValue('Ordered_Items');

            foreach ($orderItems as $orderItem) {
                $record = new Record();
                $record->addFieldValue(Products::id(), $orderItem->getKeyValue("id"));
                $record->addFieldValue(new Field('_delete'), null);

                $this->deletedItems[] = $record;
            }
        }
    }
}
