<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Mgtcreditmemoqueue\Edit;

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
        $this->setId('mgtcreditmemoqueue_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Mgtcreditmemoqueue Information'));
    }
}
