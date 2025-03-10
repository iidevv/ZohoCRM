<?php

namespace Iidev\ZohoCRM\View\Settings;

use com\zoho\api\authenticator\store\FileStore;

class GeneralSettings extends ASettings
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/general_settings.twig';
    }
    protected function isInitialized()
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
