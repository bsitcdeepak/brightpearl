<?php
namespace Bsitc\Brightpearl\Controller\Index;

class Inventorysync extends \Magento\Framework\App\Action\Action
{
    public $_inventoryhelper;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Helper\Inventory $inventoryhelper
    ) {
        $this->_inventoryhelper  = $inventoryhelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->_inventoryhelper->InventorySync();
    }
}
