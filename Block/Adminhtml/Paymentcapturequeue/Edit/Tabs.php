<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Paymentcapturequeue\Edit;

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
        $this->setId('paymentcapturequeue_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Payment Capture Queue'));
    }
}
