<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Inventoryinsert extends \Magento\Framework\App\Action\Action
{
    protected $api;
    protected $restapi;
    protected $pricelistapi;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\PricelistFactory $pricelistapi
    ) {
        $this->api = $api;
        $this->restapi = $restapi;
        $this->pricelistapi = $pricelistapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
         $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
