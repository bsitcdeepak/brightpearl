<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpcustomerreport\Edit;

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
        $this->setId('bpcustomerreport_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Bpcustomerreport Information'));
    }
}