<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use Iidev\ZohoCRM\Core\Dispatcher\CreateProfilesDispatcher;
use XLite\Core\Converter;

class ZohoProfiles extends Zoho
{
    protected function doActionCreateZohoProfiles()
    {
        $dispatcher = new CreateProfilesDispatcher();
        $message    = $dispatcher->getMessage();

        $this->bus->dispatch($message);

        $this->setReturnURL(Converter::buildURL(\Iidev\ZohoCRM\View\Tabs\Zoho::TAB_PROFILES));
    }
}
