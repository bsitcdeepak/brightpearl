<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Webhookupdate\Edit;

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
        $this->setId('webhookupdate_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Webhookupdate Information'));
    }
}
