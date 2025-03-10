<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use Iidev\ZohoCRM\Core\Dispatcher\CreateProductsDispatcher;
use XLite\Core\Converter;

class ZohoProducts extends Zoho
{
    protected function doActionCreateZohoProducts()
    {
        $dispatcher = new CreateProductsDispatcher();
        $message    = $dispatcher->getMessage();

        $this->bus->dispatch($message);

        $this->setReturnURL(Converter::buildURL(\Iidev\ZohoCRM\View\Tabs\Zoho::TAB_PRODUCTS));
    }
}
