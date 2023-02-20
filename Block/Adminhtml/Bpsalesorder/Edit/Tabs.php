<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpsalesorder\Edit;

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
        $this->setId('bpsalesorder_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Bpsalesorder Information'));
    }
}
