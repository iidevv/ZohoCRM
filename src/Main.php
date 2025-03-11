<?php

namespace Iidev\ZohoCRM;

use com\zoho\api\authenticator\store\FileStore;

class Main extends \XLite\Module\AModule
{
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
}
