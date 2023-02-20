<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Pricelistinsert extends \Magento\Framework\App\Action\Action
{
    protected $pricelistapi;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\PricelistFactory $pricelistapi
    ) {
        $this->pricelistapi = $pricelistapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->pricelistapi->getPricelistApi($range);
    }
}
