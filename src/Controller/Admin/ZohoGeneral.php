<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use XLite\Core\TopMessage;
use XLite\Core\Request;
use Iidev\ZohoCRM\Core\SDK\SDK;

class ZohoGeneral extends Zoho
{
    const OPTIONS = [
        'webhook_token',
        'store_path',
        'owner_id',
        'deleted_product_id',
        'debug_enabled',
        'orders_from_number',
        'orders_enable_sync',
        'zoho_url',
        'deals_from_date',
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
