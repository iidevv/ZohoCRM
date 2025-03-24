<?php

namespace Iidev\ZohoCRM\Module\Qualiteam\SkinActVerifiedCustomer\Model;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class VerificationInfo extends \Qualiteam\SkinActVerifiedCustomer\Model\VerificationInfo
{
    public function setStatus($status)
    {
        parent::setStatus($status);

        $zohoModel = $this->getProfile()->getZohoModel();

        if ($zohoModel) {
            $zohoModel->setSynced(false);
        }
    }
}
