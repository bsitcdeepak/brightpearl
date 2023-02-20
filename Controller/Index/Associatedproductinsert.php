<?php
namespace Bsitc\Brightpearl\Controller\Index;

class Associatedproductinsert extends \Magento\Framework\App\Action\Action
{
    
    protected $api;
    protected $restapi;
    protected $productapi;
    protected $_associatedproduct;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\BpproductsFactory $productapi,
        \Bsitc\Brightpearl\Model\AssociateproductFactory $associatedproduct,
        \Bsitc\Brightpearl\Helper\Data $datahelper
    ) {
        $this->api = $api;
        $this->restapi = $restapi;
        $this->productapi = $productapi;
        $this->_associatedproduct = $associatedproduct;
        $this->_datahelper       = $datahelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->_datahelper->setAssociateProducts();
    }
}
