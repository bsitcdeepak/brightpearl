<?php
namespace Bsitc\Brightpearl\Controller\Index;

class Attributefetch extends \Magento\Framework\App\Action\Action
{
    
    protected $api;
    protected $restapi;
    protected $productapi;
    protected $attributeapi;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\BpproductsFactory $productapi,
        \Bsitc\Brightpearl\Model\AttributeFactory $attributeapi
    ) {
        $this->api = $api;
        $this->restapi = $restapi;
        $this->productapi = $productapi;
        $this->attributeapi = $attributeapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
