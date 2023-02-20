<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Skureplacement\Edit;

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
        $this->setId('skureplacement_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Sku Replacement Information'));
    }
}