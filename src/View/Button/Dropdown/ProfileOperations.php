<?php

namespace Iidev\ZohoCRM\View\Button\Dropdown;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class ProfileOperations extends \XLite\View\Button\Dropdown\ProfileOperations
{
    /**
     * Define additional buttons
     *
     * @return array
     */
    protected function defineAdditionalButtons()
    {
        $buttons = parent::defineAdditionalButtons();

        $zohoUrl = \XLite\Core\Config::getInstance()->Iidev->ZohoCRM->zoho_url;
        if (!$zohoUrl)
            return $buttons;

        $zohoId = $this->getProfile()?->getZohoModel()?->getZohoId();

        if ($zohoId) {
            $buttons['manageInZoho'] = [
                'class' => 'Iidev\ZohoCRM\View\Button\ManageInZoho',
                'params' => [],
                'position' => 60,
            ];
        } else {
            $buttons['createZohoProfile'] = [
                'class' => 'Iidev\ZohoCRM\View\Button\CreateZohoProfile',
                'params' => [],
                'position' => 60,
            ];
        }

        return $buttons;
    }

    /**
     * Get profile
     *
     * @return \XLite\Model\Profile
     */
    protected function getProfile()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Profile')->find(
            \XLite\Core\Request::getInstance()->profile_id
        );
    }
}
