<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpordercancel\Edit;

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
        $this->setId('bpordercancel_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Bpordercancel Information'));
    }
}
