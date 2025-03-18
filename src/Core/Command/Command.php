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

class Command implements ICommand
{
    use InjectLoggerTrait;

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

    protected function processCreateResult($modelClass, $response, $type = "")
    {
        if ($response != null) {
            $actionHandler = $response->getObject();
            if ($actionHandler instanceof ActionWrapper) {
                $actionResponses = $actionHandler->getData();

                $index = 0;
                foreach ($actionResponses as $actionResponse) {
                    /** @var ZohoAwareInterface $model */
                    $model = $this->entities[$index];

                    $zohoModel = $model->getZohoModel();
                    
                    if (!$zohoModel) {
                        $zohoModel = new $modelClass();
                        $zohoModel->setId($model);
                        $model->setZohoModel($zohoModel);
                    }

                    if ($actionResponse instanceof SuccessResponse) {
                        $details = $actionResponse->getDetails();
                        if (isset($details['id'])) {
                            $zohoId = $details['id'];

                            if($type === 'quote') {
                                $zohoModel->setZohoQuoteId($zohoId);
                            } else {
                                $zohoModel->setZohoId($zohoId);
                            }
                            
                            Database::getEM()->persist($zohoModel);
                        }
                    } elseif ($actionResponse instanceof APIException) {
                        $errors = [
                            "message" => $actionResponse->getMessage() instanceof Choice ? $actionResponse->getMessage()->getValue() : $actionResponse->getMessage(),
                            "details" => $actionResponse->getDetails(),
                        ];
                        $zohoModel->setErrors(json_encode($errors));
                        $zohoModel->setSkipped(true);
                        Database::getEM()->persist($zohoModel);
                    }
                    $index++;
                }

                Database::getEM()->flush();
            }
        }
    }

    protected function processUpdateResult($response)
    {
        if ($response != null) {
            $actionHandler = $response->getObject();
            if ($actionHandler instanceof ActionWrapper) {
                $actionResponses = $actionHandler->getData();

                foreach ($actionResponses as $actionResponse) {
                    if ($actionResponse instanceof APIException) {
                        $this->getLogger('ZohoCRM')->error('processUpdateResult APIException:', [
                            $actionResponse->getStatus(),
                            $actionResponse->getCode(),
                            $actionResponse->getDetails(),
                        ]);
                    }
                }

                foreach ($this->entities as $entity) {
                    $zohoModel = $entity->getZohoModel();
                    $zohoModel->setLastSynced(time());
                    Database::getEM()->persist($zohoModel);
                }

                Database::getEM()->flush();
            }
        }
    }
}