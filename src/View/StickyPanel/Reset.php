<?php

namespace Iidev\ZohoCRM\View\StickyPanel;

use XLite\View\Button\AButton;
use Iidev\ZohoCRM\View\Button\ResetSelected;
use XLite\View\StickyPanel\ItemsListForm;

class Reset extends ItemsListForm
{
    protected function defineButtons()
    {
        $list = parent::defineButtons();

        $list['save'] = $this->getWidget(
            [
                'style' => 'more-action',
                'label' => static::t('Reset selected'),
                'disabled' => true,
                AButton::PARAM_BTN_TYPE => 'regular-button',
                'position' => 10,
            ],
            ResetSelected::class
        );

        return $list;
    }

    protected function isDisplayORLabel()
    {
        return true;
    }
}