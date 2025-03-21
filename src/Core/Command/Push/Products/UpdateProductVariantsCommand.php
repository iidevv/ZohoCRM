<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Products;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Core\Database;
use XC\ProductVariants\Model\ProductVariant;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\Record;

class UpdateProductVariantsCommand extends Command
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
            $recordOperations = new RecordOperations('Products');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getVariants();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->updateRecords($bodyWrapper, $headerInstance);

            $this->processUpdateResult(\Iidev\ZohoCRM\Model\ZohoProductVariant::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('UpdateProductVariantsCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getVariants()
    {
        $records = [];
        $this->entities = Database::getRepo(ProductVariant::class)->findByIds($this->entityIds);

        foreach ($this->entities as $variant) {
            $record = $this->getVariant($variant);
            $records[] = $record;
        }

        return $records;
    }

    protected function getVariant(ProductVariant $variant)
    {
        $record = new Record();

        $product = $variant->getProduct();
        $price = $variant->getDefaultPrice() ? $product->getPrice() : $variant->getPrice();

        $record->addFieldValue(Products::id(), $variant->getZohoModel()->getZohoId());
        $record->addFieldValue(Products::ProductCode(), $variant->getSku());
        $record->addFieldValue(Products::QtyInStock(), (double) $variant->getAmount());
        $record->addFieldValue(Products::UnitPrice(), $price);

        return $record;
    }
}
