<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Products;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Core\Database;
use XLite\Model\Product;
use XLite\Core\Config;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\util\Choice;
use Iidev\ZohoCRM\Core\Data\Converter\Main;

class PushProductsCommand extends Command
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
            $records = $this->getProducts();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);

            $this->processCreateResult(\Iidev\ZohoCRM\Model\ZohoProduct::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushProductsCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getProducts()
    {
        $shouldFlush = false;
        $records = [];
        $products = Database::getRepo(Product::class)->findByIds($this->entityIds);

        foreach ($products as $product) {
            if (!$product->hasVariants()) {
                $records[] = $this->getProduct($product);
                $this->entities[] = $product;
            } else {
                $this->setSkipProduct($product);

                Database::getEM()->persist($product);
                $shouldFlush = true;
            }
        }

        if ($shouldFlush) {
            Database::getEM()->flush();
        }

        return $records;
    }

    protected function setSkipProduct($product)
    {
        $zohoModel = $product->getZohoModel();
        if (!$zohoModel) {
            $zohoModel = new \Iidev\ZohoCRM\Model\ZohoProduct();
            $zohoModel->setId($product);
            $zohoModel->setSkipped(true);
            $product->setZohoModel($zohoModel);
        } else {
            $zohoModel->setSkipped(true);
        }
    }

    protected function getProduct(Product $product)
    {
        $record = new Record();

        $record->addFieldValue(Products::ProductName(), $product->getName());
        $record->addFieldValue(Products::ProductCode(), $product->getSku());
        $record->addFieldValue(Products::QtyInStock(), (double) $product->getAmount());
        $record->addFieldValue(Products::UnitPrice(), $product->getPrice());
        $record->addFieldValue(Products::Tax(), 0);
        $record->addFieldValue(Products::Description(), Main::getFormattedDescription($product->getBriefDescription()));
        $record->addFieldValue(Products::ProductActive(), true);

        $category = new Choice("-None-");
        $record->addFieldValue(Products::ProductCategory(), $category);

        $owner = new MinifiedUser();
        $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

        $record->addFieldValue(Products::Owner(), $owner);

        return $record;
    }
}
