<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpcurrency\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bpcurrency_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Bpcurrency Information'));
    }
}