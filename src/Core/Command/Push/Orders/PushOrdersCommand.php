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
use XLite\Core\Converter;

class PushOrdersCommand extends Command
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

        $record = $this->getKountInfo($record, $order->getOrderId());

        $date = new \DateTime('@' . $order->getDate());
        $record->addFieldValue(new Field('placedOn'), $date);

        $record->addFieldValue(Sales_Orders::Status(), new Choice($order->getShippingStatus()?->getName()));
        $record->addFieldValue(new Field('paymentStatus'), new Choice($order->getPaymentStatus()?->getName()));

        $record->addFieldValue(Sales_Orders::Subject(), "#{$order->getOrderNumber()}");
        $record->addFieldValue(new Field('entityId'), (string) $order->getOrderNumber());

        $paymentCard = $order->getCloverPaymentsCard();

        if (!empty($paymentCard)) {
            $record->addFieldValue(new Field('cardNumber'), "{$paymentCard['card_type']} {$paymentCard['card_number']} {$paymentCard['expire']}");
        }

        $record->addFieldValue(new Field('paymentMethod'), (string) $order->getPaymentMethodName());
        $record->addFieldValue(new Field('orderUrl'), (string) $this->getOrderUrl($order->getOrderNumber()));

        $record->addFieldValue(Sales_Orders::OrderedItems(), $this->getOrderItems($order->getItems()));

        $shippingAddress = $order->getProfile()?->getShippingAddress();
        $billingAddress = $order->getProfile()?->getBillingAddress();

        $record = $this->getShippingAddress($record, Sales_Orders::class, $shippingAddress);
        $record = $this->getBillingAddress($record, Sales_Orders::class, $billingAddress);

        $discount = $order->getSurchargeSumByType(Surcharge::TYPE_DISCOUNT);
        $record->addFieldValue(Sales_Orders::Discount(), (double) abs($discount));

        $record->addFieldValue(Sales_Orders::Adjustment(), $this->getAdjustment($order));

        $record->addFieldValue(new Field('customerNotes'), $this->getNotes($order->getNotes()));

        $record->addFieldValue(new Field('staffNotes'), $this->getNotes($order->getAdminNotes()));

        $profileId = $order->getOrigProfile()?->getZohoModel()?->getZohoId();

        if ($profileId) {
            $profile = new Record();
            $profile->setId($profileId);
            $record->addFieldValue(Sales_Orders::ContactName(), $profile);
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

    protected function getKountInfo($record, $orderId)
    {
        $inquiry = \XLite\Core\Database::getRepo(\Iidev\Kount\Model\InquiryOrders::class)->findOneBy([
            'orderid' => $orderId
        ]);

        if (!$inquiry)
            return $record;

        $record->addFieldValue(new Field('kountUrl'), "https://awc.kount.net/workflow/detail.html?id={$inquiry->getTransactionId()}");
        $record->addFieldValue(new Field('kountScore'), (string) $inquiry->getScore());
        $record->addFieldValue(new Field('kountOmniscore'), (string) $inquiry->getOmniscore());
        $record->addFieldValue(new Field('kountWarnings'), $inquiry->getWarnings());
        $record->addFieldValue(new Field('kountIp'), "https://whatismyipaddress.com/ip/{$inquiry->getIpAddress()}");
        $record->addFieldValue(new Field('kountZipcode'), (string) $inquiry->getZipcode());

        return $record;
    }

    protected function getOrderUrl($orderNumber)
    {
        return Converter::buildFullURL(
            'order',
            '',
            ['order_number' => $orderNumber],
            \XLite::getAdminScript()
        );
    }
}
