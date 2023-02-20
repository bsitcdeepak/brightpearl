<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpsalescredit\Edit;

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
        $this->setId('bpsalescredit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('BP Sales Credit Information'));
    }
}
