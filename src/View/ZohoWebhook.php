<?php

namespace Iidev\ZohoCRM\View;

use XCart\Extender\Mapping\ListChild;

/**
 * @ListChild (list="center")
 */
class ZohoWebhook extends \XLite\View\AView
{
    /**
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), ['zoho_webhook']);
    }

    /**
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return '';
    }
}