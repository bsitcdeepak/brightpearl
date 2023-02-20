<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Productpricesync extends \Magento\Framework\App\Action\Action
{

    public $_productupdatehelper;
     
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Helper\Productupdate $productupdatehelper
    ) {
        $this->_productupdatehelper  = $productupdatehelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->_productupdatehelper->ProductupdateSync();
        /*Price Update funationality*/
        $this->_productupdatehelper->UpdateProductPriceBysync();
    }
}
