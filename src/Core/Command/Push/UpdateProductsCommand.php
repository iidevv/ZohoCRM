<?php

namespace Iidev\ZohoCRM\Core\Command\Push;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Core\Database;
use XLite\Model\Product;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\APIException;
use XLite\InjectLoggerTrait;

class UpdateProductsCommand extends Command
{
    use InjectLoggerTrait;

    private array $productIds;
    private array $products = [];

    public function __construct(
        array $productIds
    ) {
        parent::__construct();
        $this->productIds = $productIds;
    }

    public function execute(): void
    {
        try {
            $recordOperations = new RecordOperations('Products');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getProducts();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->updateRecords($bodyWrapper, $headerInstance);

            $this->processResult($response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('UpdateProductsCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
                        ]);
                    }

                    foreach ($this->products as $product) {
                        $product->setZohoLastSynced(time());
                        Database::getEM()->persist($product);
                    }

                    Database::getEM()->flush();
                }
            }
        }
    }

    protected function getProducts()
    {
        $records = [];
        $this->products = Database::getRepo(Product::class)->findByIds($this->productIds);

        foreach ($this->products as $product) {
            if ($product->hasVariants()) {
                $records = array_merge($records, $this->getVariants($product));
            } else {
                $records[] = $this->getProduct($product);
            }
        }

        return $records;
    }

    protected function getProduct(Product $product)
    {
        $record = new Record();

        $record->addFieldValue(Products::id(), $product->getZohoId());
        $record->addFieldValue(Products::ProductCode(), $product->getSku());
        $record->addFieldValue(Products::QtyInStock(), (double) $product->getAmount());
        $record->addFieldValue(Products::UnitPrice(), $product->getPrice());

        return $record;
    }

    protected function getVariants(Product $product)
    {
        $records = [];
        $variants = $product->getVariants();

        foreach ($variants as $variant) {
            $record = new Record();

            $record->addFieldValue(Products::id(), $variant->getZohoId());
            $record->addFieldValue(Products::ProductCode(), $variant->getSku());
            $record->addFieldValue(Products::QtyInStock(), (double) $variant->getAmount());
            $record->addFieldValue(Products::UnitPrice(), $variant->getPrice());

            $records[] = $record;
        }

        return $records;
    }
}
