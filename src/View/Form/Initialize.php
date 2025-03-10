<?php

namespace Iidev\ZohoCRM\View\Form;

/**
 * Initialize form
 */
class Initialize extends \XLite\View\Form\ItemsList\AItemsList
{
    /**
     * Return default value for the "target" parameter
     *
     * @return string
     */
    protected function getDefaultTarget()
    {
        return 'zoho_general';
    }

    /**
     * Return default value for the "action" parameter
     *
     * @return string
     */
    protected function getDefaultAction()
    {
        return 'initialize';
    }
}
