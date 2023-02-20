<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Productcreate extends \Magento\Framework\App\Action\Action
{

    protected $restapi;
    
    protected $datahelper;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\RestApi $restapi
    ) {
        $this->restapi = $restapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
         /*Product Create*/
          $this->restapi->setProductsApi();
    }
}
