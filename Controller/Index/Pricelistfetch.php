<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Pricelistfetch extends \Magento\Framework\App\Action\Action
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
         $this->pricelistapi->getPricelistApi();
    }
}
