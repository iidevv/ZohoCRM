<?php

namespace Iidev\ZohoCRM\View\Button;

class ManageInZoho extends \XLite\View\Button\Link
{
    /**
     * Get default CSS class name
     *
     * @return string
     */
    protected function getDefaultStyle()
    {
        return 'action manage-in-zoho always-enabled';
    }

    /**
     * Get default label
     * todo: move translation here
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return 'Manage in Zoho';
    }

    /**
     * We make the full location path for the provided URL
     *
     * @return string
     */
    protected function getLocationURL()
    {
        $zohoUrl = \XLite\Core\Config::getInstance()->Iidev->ZohoCRM->zoho_url;
        if (!$zohoUrl)
            return null;

        $zohoId = $this->getProfile()?->getZohoModel()?->getZohoId();
        if (!$zohoId)
            return null;

        return "{$zohoUrl}/tab/Contacts/{$zohoId}";
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

    /**
     * Return true if button is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->isProfileAllowed();
    }

    /**
     * Return true if profile meets conditions
     *
     * @return boolean
     */
    protected function isProfileAllowed()
    {
        return $this->getProfile()
            && $this->getProfile()->isPersistent()
            && !$this->getProfile()->getAnonymous();
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams[self::PARAM_BLANK]->setValue(true);
    }
}
