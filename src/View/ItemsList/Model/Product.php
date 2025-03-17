<?php

namespace Iidev\ZohoCRM\View\ItemsList\Model;

use Iidev\ZohoCRM\View\Tabs\Zoho;

class Product extends \XLite\View\ItemsList\Model\Product\Admin\Search
{
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();

        $list[] = Zoho::TAB_PRODUCTS;

        return $list;
    }

    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        return $list;
    }

    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        return $list;
    }

    /**
     * @inheritDoc
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $result = [];

        foreach ($columns as $columnName => $columnData) {
            if (!in_array($columnName, ['name'])) {
                continue;
            }
            $result[$columnName] = $columnData;
        }

        $result['zoho_skipped'] = [
            static::COLUMN_NAME => 'Is skipped',
            static::COLUMN_ORDERBY => 900,
            static::COLUMN_MAIN => false,
            static::COLUMN_NO_WRAP => false,
            static::COLUMN_TEMPLATE => 'modules/Iidev/ZohoCRM/items_list/skipped.twig',
        ];

        $result['zoho_errors'] = [
            static::COLUMN_NAME => 'Errors',
            static::COLUMN_ORDERBY => 1000,
            static::COLUMN_MAIN => false,
            static::COLUMN_NO_WRAP => true,
            static::COLUMN_TEMPLATE => 'modules/Iidev/ZohoCRM/items_list/errors.twig',
        ];

        return $result;
    }

    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }

    /**
     * @param \XLite\Model\Product $entity Entity
     *
     * @return bool
     */
    protected function removeEntity(\XLite\Model\AEntity $entity)
    {
        $productId = $entity ? $entity->getProductId() : null;

        if ($productId) {
            \XLite\Core\Database::getRepo(\Iidev\ZohoCRM\Model\ZohoProduct::class)
                ->deleteEntities($productId);

            return true;
        }

        return false;
    }


    /**
     * @return boolean
     */
    protected function isSelectable()
    {
        return true;
    }

    protected function getRightActions()
    {
        return [];
    }

    protected function getLeftActions()
    {
        return parent::getLeftActions();
    }

    /**
     * Inline creation mechanism position
     *
     * @return integer
     */
    protected function isInlineCreation()
    {
        return static::CREATE_INLINE_NONE;
    }

    /**
     * @return string
     */
    protected function getCreateURL()
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return '';
    }

    /**
     * @return boolean
     */
    protected function isExportable()
    {
        return false;
    }

    protected function wrapWithFormByDefault()
    {
        return true;
    }

    protected function getFormTarget()
    {
        return Zoho::TAB_PRODUCTS;
    }

    /**
     * Get search form options
     *
     * @return array
     */
    public function getSearchFormOptions()
    {
        return [
            'target' => Zoho::TAB_PRODUCTS,
        ];
    }

    /**
     * Get search panel widget class
     *
     * @return string
     */
    protected function getSearchPanelClass()
    {
        return 'XLite\View\SearchPanel\Product\Admin\Main';
    }

    /**
     * Get panel class
     *
     * @return string|\XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'Iidev\ZohoCRM\View\StickyPanel\Reset';
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        $result->{\XLite\Model\Repo\Product::SEARCH_ZOHO_PRODUCTS} = true;

        return $result;
    }

    /**
     * @return array
     */
    protected function getAttributes()
    {
        return [
            'data-widget' => 'Iidev\ZohoCRM\View\ItemsList\Model\Product'
        ];
    }
}