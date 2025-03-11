<?php

namespace Iidev\ZohoCRM\View\Settings;

class GeneralSettings extends ASettings
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/general_settings.twig';
    }
    protected function isInitialized()
    {
        return (new \Iidev\ZohoCRM\Main())->isInitialized();
    }
}
