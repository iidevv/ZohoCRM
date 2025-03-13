<?php

namespace Iidev\ZohoCRM\Core\Command\Push;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Core\Database;
use XC\ProductVariants\Model\ProductVariant;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\APIException;
use XLite\InjectLoggerTrait;

class UpdateProductVariantsCommand extends Command
{
    use InjectLoggerTrait;

    private array $entityIds;
    private array $variants = [];

    public function __construct(
        array $entityIds
    ) {
        parent::__construct();
        $this->entityIds = $entityIds;
    }

    public function execute(): void
    {
        try {
            $recordOperations = new RecordOperations('Products');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getVariants();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->updateRecords($bodyWrapper, $headerInstance);

            $this->processResult($response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('UpdateProductVariantsCommand Error:', [
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

                foreach ($actionResponses as $actionResponse) {
                    if ($actionResponse instanceof APIException) {
                        $this->getLogger('ZohoCRM')->error('APIException:', [
                            $actionResponse->getStatus(),
                            $actionResponse->getCode(),
                            $actionResponse->getDetails(),
                        ]);
                    }

                    foreach ($this->variants as $variant) {
                        $variant->setZohoLastSynced(time());
                        Database::getEM()->persist($variant);
                    }

                    Database::getEM()->flush();
                }
            }
        }
    }

    protected function getVariants()
    {
        $records = [];
        $this->variants = Database::getRepo(ProductVariant::class)->findByIds($this->entityIds);

        foreach ($this->variants as $variant) {
            $record = $this->getVariant($variant);
            $records[] = $record;
        }

        return $records;
    }

    protected function getVariant(ProductVariant $variant)
    {
        $record = new Record();

        $record->addFieldValue(Products::id(), $variant->getZohoId());
        $record->addFieldValue(Products::ProductCode(), $variant->getSku());
        $record->addFieldValue(Products::QtyInStock(), (double) $variant->getAmount());
        $record->addFieldValue(Products::UnitPrice(), $variant->getPrice());

        return $record;
    }
}
