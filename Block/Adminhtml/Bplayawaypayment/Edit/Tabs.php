<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bplayawaypayment\Edit;

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
        $this->setId('bplayawaypayment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Bplayawaypayment Information'));
    }
}
