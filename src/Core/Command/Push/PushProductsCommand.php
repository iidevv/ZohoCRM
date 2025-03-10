<?php

namespace Iidev\ZohoCRM\Core\Command\Push;

use Exception;
use Iidev\ZohoCRM\Core\Command\ICommand;
use XLite\Core\Database;
use XLite\Model\Product;
use Iidev\ZohoCRM\Core\SDK\SDK;
use XLite\Core\Config;
use com\zoho\crm\api\record\GetRecordsParam;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\GetRecordsHeader;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\tags\Tag;
use com\zoho\crm\api\record\Products;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\util\Choice;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\record\APIException;
use XLite\InjectLoggerTrait;

class PushProductsCommand implements ICommand
{
    use InjectLoggerTrait;

    private array $productIds;

    public function __construct(
        array $productIds
    ) {
        (new SDK())->initialize([]);
        $this->productIds = $productIds;
    }

    public function execute(): void
    {
        try {
            $recordOperations = new RecordOperations('Products');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getProducts();

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);
           
            $this->processResult($response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('', [
                $e->getMessage(),
            ]);
        }
    }

    protected function processResult($response)
    {
        if ($response != null) {
            echo ("Status Code: " . $response->getStatusCode() . "<br>");
            $actionHandler = $response->getObject();
            if ($actionHandler instanceof ActionWrapper) {
                $actionWrapper = $actionHandler;
                $actionResponses = $actionWrapper->getData();
                foreach ($actionResponses as $actionResponse) {
                    if ($actionResponse instanceof SuccessResponse) {
                        $successResponse = $actionResponse;
                        echo ("Status: " . $successResponse->getStatus()->getValue() . "<br>");
                        echo ("Code: " . $successResponse->getCode()->getValue() . "<br>");
                        echo ("Details: ");
                        foreach ($successResponse->getDetails() as $key => $value) {
                            echo ($key . " : ");
                            print_r($value);
                            echo ("<br>");
                        }
                        echo ("Message: " . ($successResponse->getMessage() instanceof Choice ? $successResponse->getMessage()->getValue() : $successResponse->getMessage()) . "<br>");
                    } else if ($actionResponse instanceof APIException) {
                        $exception = $actionResponse;
                        echo ("Status: " . $exception->getStatus()->getValue() . "<br>");
                        echo ("Code: " . $exception->getCode()->getValue() . "<br>");
                        echo ("Details: ");
                        foreach ($exception->getDetails() as $key => $value) {
                            echo ($key . " : ");
                            print_r($value);
                            echo ("<br>");
                        }
                        echo ("Message : " . ($exception->getMessage() instanceof Choice ? $exception->getMessage()->getValue() : $exception->getMessage()) . "<br>");
                    }
                }
            } else if ($actionHandler instanceof APIException) {
                $exception = $actionHandler;
                echo ("Status: " . $exception->getStatus()->getValue() . "<br>");
                echo ("Code: " . $exception->getCode()->getValue() . "<br>");
                echo ("Details: ");
                foreach ($exception->getDetails() as $key => $value) {
                    echo ($key . " : " . $value . "<br>");
                }
                echo ("Message : " . ($exception->getMessage() instanceof Choice ? $exception->getMessage()->getValue() : $exception->getMessage()) . "<br>");
            }
        }
        die();
        // if (
        //     isset($result['Status'])
        //     && $result['Status'] === self::STATUS_SUCCESS
        // ) {
        //     foreach ($this->productIds as $id) {
        //         /** @var Product $product */
        //         $product = Database::getRepo(Product::class)->find($id);
        //         $product->setIsSkuvaultSynced(true);
        //         $product->setIsSkuvaultUpdateSynced(true);
        //     }

        //     Database::getEM()->flush();
        // }
    }

    protected function getProducts()  {
        $records = [];
        $products = Database::getRepo(Product::class)->findByIds($this->productIds);

        foreach ($products as $product) {
            $records[] = $this->getProduct($product);
        }

        return $records;
    }

    protected function getProduct(Product $product) {
        $record = new Record();
        $category = new Choice("-None-");
        $record->addFieldValue(Products::ProductCategory(), $category);
        $record->addFieldValue(Products::QtyInDemand(), $product->getAmount());
        $owner = new MinifiedUser();
        $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

        // updateRecords
        // $record->addFieldValue(Products::id(), "6530264000000618002");
        $record->addFieldValue(Products::Owner(), $owner);
        $record->addFieldValue(Products::Description(), $product->getBriefDescription());
        $record->addFieldValue(Products::Tax(), 0);
        $record->addFieldValue(Products::ProductActive(), true);
        $record->addFieldValue(Products::ProductCode(), $product->getSku());
        // $manufacturer = new Choice("manufacturer1");
        // $record->addFieldValue(Products::Manufacturer(), $manufacturer);
        $record->addFieldValue(Products::ProductName(), $product->getName());

        return $record;
    }
}
