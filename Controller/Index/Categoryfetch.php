<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Categoryfetch extends \Magento\Framework\App\Action\Action
{
    protected $api;

    protected $restapi;

    protected $categoryapi;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\CategoryFactory $categoryapi
    ) {
        $this->api = $api;
        $this->restapi = $restapi;
        $this->categoryapi = $categoryapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->categoryapi->setCategoryApi();
        /*$this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();*
        */
    }
}
