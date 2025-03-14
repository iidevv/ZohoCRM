<?php

namespace Iidev\ZohoCRM;

use XLite\Core\Converter;
use XLite\Core\Config;
use XLite\Model\Product;
use XLite\Core\Database;
use com\zoho\api\authenticator\store\FileStore;

class Main extends \XLite\Module\AModule
{
    /**
     * Return link to settings form
     *
     * @return string
     */
    public static function getSettingsForm()
    {
        return Converter::buildURL(\Iidev\ZohoCRM\View\Tabs\Zoho::TAB_GENERAL);
    }

    public function isInitialized()
    {
        $tokenstore = new FileStore("../zoho_sdk_token.txt");
        try {
            $tokenstore->findTokenById(1);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getDeletedProductPlaceholder()
    {
        $deletedProductPlaceholder = Database::getRepo(Product::class)->findOneBy(['product_id' => Config::getInstance()->Iidev->ZohoCRM->deleted_product_id]);

        if (!$deletedProductPlaceholder) {
            throw new \Exception('Deleted product placeholder not found');
        }

        return $deletedProductPlaceholder;
    }
}
