<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Inventoryfetch extends \Magento\Framework\App\Action\Action
{
    protected $api;

    protected $restapi;

    protected $productinventory;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\ProductinventoryFactory $productinventory
    ) {
        $this->api = $api;
        $this->restapi = $restapi;
        $this->_productinventory = $productinventory;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->_productinventory->Productinventory();
    }
}
