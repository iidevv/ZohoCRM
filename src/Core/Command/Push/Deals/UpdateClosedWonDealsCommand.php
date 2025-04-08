<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Deals;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Deals;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;
use \Iidev\ZohoCRM\Model\ZohoDeal;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\record\APIException;
use XLite\Core\Database;

class UpdateClosedWonDealsCommand extends Command
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
         
            $this->processUpdateResult(ZohoDeal::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('UpdateClosedWonDealsCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getCarts()
    {
        $records = [];
        $zohoDeals = Database::getRepo(ZohoDeal::class)->findByIds($this->entityIds);

        foreach ($zohoDeals as $zohoDeal) {
            $records[] = $this->getCart($zohoDeal);
            $this->entities[] = $zohoDeal;
        }

        return $records;
    }

    protected function getCart(ZohoDeal $zohoDeal)
    {
        $zohoId = $zohoDeal->getZohoId();

        $record = new Record();

        $record->addFieldValue(Deals::id(), $zohoId);
        
        $lastVisitDate = new \DateTime('@' . $zohoDeal->getId()?->getLastVisitDate());
        $record->addFieldValue(new Field('lastVisitDate'), $lastVisitDate);

        $orderId = $zohoDeal->getId()?->getZohoModel()?->getZohoId();

        if ($orderId) {
            $order = new Record();
            $order->setId($orderId);
            $record->addFieldValue(new Field('orderNumber'), $order);
        }

        if ($orderId) {
            $record->addFieldValue(Deals::Stage(), new Choice('Closed Won'));
        }

        return $record;
    }


    protected function processUpdateResult($modelClass, $response)
    {
        if ($response != null) {
            $actionHandler = $response->getObject();
           
            if ($actionHandler instanceof ActionWrapper) {
                $actionResponses = $actionHandler->getData();
               
                $index = 0;
                foreach ($actionResponses as $actionResponse) {
                    $zohoModel = $this->entities[$index];

                    if ($actionResponse instanceof SuccessResponse) {
                        $zohoModel->setSkipped(true);
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
}
