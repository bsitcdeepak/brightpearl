<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Thirdpartyorders\Edit;

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
        $this->setId('thirdpartyorders_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Thirdpartyorders Information'));
    }
}