<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use Iidev\ZohoCRM\Core\Dispatcher\CreateOrdersDispatcher;
use XLite\Core\Converter;

class ZohoOrders extends Zoho
{
    const OPTIONS = [
        'orders_from_number',
    ];

    protected function doActionCreateZohoOrders()
    {
        $dispatcher = new CreateOrdersDispatcher();
        $message    = $dispatcher->getMessage();
       
        $this->bus->dispatch($message);

        $this->setReturnURL(Converter::buildURL(\Iidev\ZohoCRM\View\Tabs\Zoho::TAB_ORDERS));
    }
}
