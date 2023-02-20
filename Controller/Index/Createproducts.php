<?php
namespace Bsitc\Brightpearl\Controller\Index;

class Createproducts extends \Magento\Framework\App\Action\Action
{

    protected $api;
    protected $restapi;
    protected $productapi;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\BpproductsFactory $productapi
    ) {
        $this->api = $api;
        $this->restapi = $restapi;
        $this->productapi = $productapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->restapi->setProductsApi();
    }
}
