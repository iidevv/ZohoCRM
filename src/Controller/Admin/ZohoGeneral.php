<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use XLite\Core\TopMessage;
use XLite\Core\Request;
use Iidev\ZohoCRM\Core\SDK\SDK;

class ZohoGeneral extends Zoho
{
    const OPTIONS = [
        'owner_id',
        'deleted_product_id',
        'debug_enabled',
    ];

    protected function doActionInitialize()
    {
        $data = Request::getInstance()->getData();
        try {
            (new SDK())->initialize($data);
            TopMessage::addInfo('Successfully initialized');
        } catch (\Exception $e) {
            TopMessage::addError($e->getMessage());
        }
    }
}
