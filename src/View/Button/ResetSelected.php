<?php

namespace Iidev\ZohoCRM\View\Button;

/**
 * 'Reset errors for selected' button
 */
class ResetSelected extends \XLite\View\Button\DeleteSelected
{
    /**
     * getDefaultLabel
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return static::t('Reset selected');
    }

    /**
     * getDefaultLabel
     *
     * @return string
     */
    protected function getDefaultTitle()
    {
        return '';
    }

    /**
     * getDefaultAction
     *
     * @return string
     */
    protected function getDefaultAction()
    {
        return 'delete';
    }

    /**
     * getDefaultConfirmationText
     *
     * @return string
     */
    protected function getDefaultConfirmationText()
    {
        return 'Do you really want to reset selected errors?';
    }
}