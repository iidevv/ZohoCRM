<?php

namespace Iidev\ZohoCRM\View\Button;

class CreateZohoProfile extends \XLite\View\Button\Link
{
    /**
     * Get default CSS class name
     *
     * @return string
     */
    protected function getDefaultStyle()
    {
        return 'action create-zoho-profile always-enabled';
    }

    /**
     * Get default label
     * todo: move translation here
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return 'Sync Profile to Zoho';
    }

    /**
     * We make the full location path for the provided URL
     *
     * @return string
     */
    protected function getLocationURL()
    {
        return $this->buildURL('zoho_profiles', 'create_zoho_profile', [
            'id' => $this->getProfile()->getProfileId()
        ]);
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

}
