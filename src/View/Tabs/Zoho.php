<?php

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace Iidev\ZohoCRM\View\Tabs;

use Iidev\ZohoCRM\View\Settings as Settings;
use XCart\Extender\Mapping\ListChild;
use XLite\View\Tabs\ATabs;

/**
 * @ListChild (list="admin.center", zone="admin", weight="100")
 */
class Zoho extends ATabs
{
    const TAB_GENERAL          = 'zoho_general';
    const TAB_PRODUCTS          = 'zoho_products';
    const TAB_USERS          = 'zoho_users';
    const TAB_ORDERS          = 'zoho_orders';

    /**
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(
            parent::getAllowedTargets(),
            [
                static::TAB_GENERAL,
                static::TAB_PRODUCTS,
                static::TAB_USERS,
                static::TAB_ORDERS
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function defineTabs()
    {
        return [
            static::TAB_GENERAL          => [
                'weight' => 100,
                'title'  => static::t('General Settings'),
                'widget' => Settings\GeneralSettings::class,
            ],
            static::TAB_PRODUCTS         => [
                'weight' => 200,
                'title'  => static::t('Products'),
                'widget' => Settings\Products::class,
            ],
            static::TAB_USERS        => [
                'weight' => 200,
                'title'  => static::t('Users'),
                'widget' => Settings\Users::class,
            ],
            static::TAB_ORDERS         => [
                'weight' => 200,
                'title'  => static::t('Orders'),
                'widget' => Settings\Orders::class,
            ],
        ];
    }
}
