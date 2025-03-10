<?php

namespace Iidev\ZohoCRM\View\Menu\Admin;

use Iidev\ZohoCRM\View\Tabs\Zoho;
use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class LeftMenu extends \XLite\View\Menu\Admin\LeftMenu
{
    protected function defineItems()
    {
        $items = parent::defineItems();

        $items['store_setup'][static::ITEM_CHILDREN]['zoho_settings'] = [
            static::ITEM_TITLE  => static::t('ZohoCRM settings'),
            static::ITEM_TARGET => Zoho::TAB_GENERAL,
            static::ITEM_WEIGHT => 920,
        ];

        return $items;
    }
}
