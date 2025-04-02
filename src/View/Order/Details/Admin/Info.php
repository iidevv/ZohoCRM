<?php

namespace Iidev\ZohoCRM\View\Order\Details\Admin;

use XLite\Core\Config;
use XCart\Extender\Mapping\Extender;

/**
 * Order info
 * 
 * @Extender\Mixin
 */
class Info extends \XLite\View\Order\Details\Admin\Info
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/Iidev/ZohoCRM/order/invoice/style.css';

        return $list;
    }

    public function getZohoUrl()
    {
        $zohoUrl = Config::getInstance()->Iidev->ZohoCRM->zoho_url;
        if (!$zohoUrl)
            return null;

        $zohoId = $this->getOrder()?->getZohoModel()?->getZohoId();
        if (!$zohoId)
            return null;

        return "{$zohoUrl}/tab/SalesOrders/{$zohoId}";
    }

    public function getOrderId()
    {
        return $this->getOrder()->getOrderId();
    }
}
