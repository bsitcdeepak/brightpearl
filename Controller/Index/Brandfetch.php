<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Brandfetch extends \Magento\Framework\App\Action\Action
{

    protected $api;

    protected $restapi;

    protected $brandapi;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\BrandFactory $brandapi
    ) {
        $this->api = $api;
        $this->restapi = $restapi;
        $this->brandapi = $brandapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->brandapi->syncBrandApi();
         /*$this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();*
        */
    }
}
