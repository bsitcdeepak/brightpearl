<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Warehouse\Edit;

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
        $this->setId('warehouse_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Warehouse Information'));
    }
}
