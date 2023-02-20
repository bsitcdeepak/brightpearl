<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Builderstock\Edit;

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
        $this->setId('builderstock_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Builderstock Information'));
    }
}