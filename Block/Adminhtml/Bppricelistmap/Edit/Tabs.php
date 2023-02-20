<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bppricelistmap\Edit;

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
        $this->setId('bppricelistmap_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Price List Mapping'));
    }
}
