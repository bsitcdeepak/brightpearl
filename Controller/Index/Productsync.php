<?php
namespace Bsitc\Brightpearl\Controller\Index;

class Productsync extends \Magento\Framework\App\Action\Action
{

    protected $_productupdatehelper;
    

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Helper\Productupdate $productupdatehelper
    ) {
        $this->_productupdatehelper  = $productupdatehelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->_productupdatehelper->ProductupdateSync();
    }
}
