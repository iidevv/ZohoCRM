<?php

namespace Iidev\ZohoCRM\View\Settings;

use Iidev\ZohoCRM\View\Tabs\Zoho;

abstract class ASettings extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(
            parent::getAllowedTargets(),
            [
                Zoho::TAB_GENERAL,
                Zoho::TAB_PRODUCTS,
                Zoho::TAB_USERS,
                Zoho::TAB_ORDERS
            ]
        );
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/Iidev/ZohoCRM/settings';
    }
}
