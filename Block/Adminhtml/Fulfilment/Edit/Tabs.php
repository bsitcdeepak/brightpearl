<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Fulfilment\Edit;

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
        $this->setId('fulfilment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Fulfilment Information'));
    }
}
