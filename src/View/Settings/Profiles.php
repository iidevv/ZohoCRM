<?php

namespace Iidev\ZohoCRM\View\Settings;

class Profiles extends ASettings
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/profiles.twig';
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/tabs.css';

        return $list;
    }
}
