<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Mgapi extends \Magento\Framework\App\Action\Action
{
    
    protected $_restapi;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\RestApi $restapi
    ) {
        $this->_restapi = $restapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->_restapi->setAttributeOptions();

        $this->_restapi->setSeasonOptions();
        $this->_restapi->setBpSeasonOptions();

        $attcode = 'bp_brand';
        $this->_restapi->setBrandOptions($attcode);

        // $this->_restapi->setCategoryApi();

        $code = 'bp_collection';
        $this->_restapi->setCollectionOption($code);

        //$this->_restapi->setProductsApi();
    }
}
