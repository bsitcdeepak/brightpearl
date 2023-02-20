<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Bpwarehouse extends \Magento\Framework\App\Action\Action
{
    protected $api;

    protected $restapi;

    protected $pricelistconfig;
    
    protected $bpwarehouse;
        
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\CategoryFactory $categoryapi,
        \Bsitc\Brightpearl\Model\PricelistconfigFactory $pricelistconfig,
        \Bsitc\Brightpearl\Model\BpwarehouseFactory $bpwarehouse
    ) {
        $this->api = $api;
        $this->restapi = $restapi;
        $this->categoryapi = $categoryapi;
        $this->pricelistconfig = $pricelistconfig;
        $this->bpwarehouse = $bpwarehouse;
        
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->bpwarehouse->setBpwarehouse();
    }
}
