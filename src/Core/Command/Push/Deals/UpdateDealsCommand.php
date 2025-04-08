<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Deals;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Model\Order;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Deals;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;

class UpdateDealsCommand extends Command
{
    public function __construct(
        array $entityIds,
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
            $recordOperations = new RecordOperations('Deals');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getCarts();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->updateRecords($bodyWrapper, $headerInstance);

            $this->processUpdateResult(\Iidev\ZohoCRM\Model\ZohoDeal::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('UpdateDealsCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getCart(Order $order)
    {
        $zohoId = $order->getZohoDeal()?->getZohoId();

        $record = new Record();

        $record->addFieldValue(Deals::id(), $zohoId);

        $lastVisitDate = new \DateTime('@' . $order->getLastVisitDate());
        $record->addFieldValue(new Field('lastVisitDate'), $lastVisitDate);

        if ($order->getLost()) {
            $record->addFieldValue(Deals::Stage(), new Choice('Closed Lost'));
        }

        if ($order->getTotal() !== $order->getZohoDeal()->getTotal()) {
            $record->addFieldValue(Deals::Amount(), (double) abs($order->getTotal()));

            $record->addFieldValue(Deals::Description(), $this->getItemsDescription($order->getItems()));
        }

        return $record;
    }
}
