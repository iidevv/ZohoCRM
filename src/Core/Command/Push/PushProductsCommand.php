<?php

namespace Iidev\ZohoCRM\Core\Command\Push;

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
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\record\APIException;
use Iidev\ZohoCRM\Core\Data\Converter\Main;
use XLite\InjectLoggerTrait;

class PushProductsCommand extends Command
{
    use InjectLoggerTrait;

    private array $productIds;
    private array $entities = [];

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
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);

            $this->processResult($response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushProductsCommand Error:', [
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

                $index = 0;
                foreach ($actionResponses as $actionResponse) {

                    $product = $this->entities[$index];

                    if ($actionResponse instanceof SuccessResponse) {
                        $details = $actionResponse->getDetails();
                        if (isset($details['id'])) {
                            $zohoId = $details['id'];
                            
                            $product->setZohoId($zohoId);
                        }
                    } else if ($actionResponse instanceof APIException) {
                        $this->getLogger('ZohoCRM')->error('APIException:', [
                            $product->getId(),
                            $actionResponse->getDetails(),
                        ]);
                    }
                    $index++;
                }

                Database::getEM()->flush();
            }
        }
    }

    protected function getProducts()
    {
        $records = [];
        $products = Database::getRepo(Product::class)->findByIds($this->productIds);

        foreach ($products as $product) {
            if ($product->hasVariants()) {
                $product->setZohoId('has_variants');
                $records = array_merge($records, $this->getVariants($product));
            } else {
                $records[] = $this->getProduct($product);
                $this->entities[] = $product;
            }
        }

        return $records;
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

    protected function getVariants(Product $product)
    {
        $records = [];
        $variants = $product->getVariants();

        foreach ($variants as $variant) {
            $record = new Record();

            $record->addFieldValue(Products::ProductName(), $this->getVariantTitle($variant));
            $record->addFieldValue(Products::ProductCode(), $variant->getSku());
            $record->addFieldValue(Products::QtyInStock(), (double) $variant->getAmount());
            $record->addFieldValue(Products::UnitPrice(), $variant->getPrice());
            $record->addFieldValue(Products::Tax(), 0);
            $record->addFieldValue(Products::Description(), Main::getFormattedDescription($product->getBriefDescription()));
            $record->addFieldValue(Products::ProductActive(), true);

            $category = new Choice("-None-");
            $record->addFieldValue(Products::ProductCategory(), $category);

            $owner = new MinifiedUser();
            $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

            $record->addFieldValue(Products::Owner(), $owner);

            $records[] = $record;

            $this->entities[] = $variant;
        }

        return $records;
    }

    protected function getVariantTitle(\XC\ProductVariants\Model\ProductVariant $model)
    {
        $attrsString = array_reduce($model->getValues(), static function ($str, $attr) {
            $str .= $attr->asString() . ' ';
            return $str;
        }, '');

        return $model->getProduct()->getName() . ' ' . trim($attrsString);
    }
}
