<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use Iidev\ZohoCRM\Core\Dispatcher\CreateProductsDispatcher;
use Iidev\ZohoCRM\Core\Dispatcher\CreateProductVariantsDispatcher;
use Iidev\ZohoCRM\Core\Dispatcher\UpdateProductsDispatcher;
use Iidev\ZohoCRM\Core\Dispatcher\UpdateProductVariantsDispatcher;
use XLite\Core\Converter;

class ZohoProducts extends Zoho
{
    protected function doActionCreateZohoProducts()
    {
        $dispatcherProducts = new CreateProductsDispatcher();
        $message    = $dispatcherProducts->getMessage();
        
        $this->bus->dispatch($message);

        $dispatcherVariants = new CreateProductVariantsDispatcher();
        $message    = $dispatcherVariants->getMessage();
        
        $this->bus->dispatch($message);

        $this->setReturnURL(Converter::buildURL(\Iidev\ZohoCRM\View\Tabs\Zoho::TAB_PRODUCTS));
    }

    protected function doActionUpdateZohoProducts()
    {
        $dispatcherProducts = new UpdateProductsDispatcher();
        $message    = $dispatcherProducts->getMessage();
        
        $this->bus->dispatch($message);

        $dispatcherVariants = new UpdateProductVariantsDispatcher();
        $message    = $dispatcherVariants->getMessage();
        
        $this->bus->dispatch($message);

        $this->setReturnURL(Converter::buildURL(\Iidev\ZohoCRM\View\Tabs\Zoho::TAB_PRODUCTS));
    }
}
