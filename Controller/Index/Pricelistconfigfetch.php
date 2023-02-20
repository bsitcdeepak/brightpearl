<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Pricelistconfigfetch extends \Magento\Framework\App\Action\Action
{

    protected $pricelistconfig;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\PricelistconfigFactory $pricelistconfig
    ) {
        $this->pricelistconfig = $pricelistconfig;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->pricelistconfig->getAllPriceListConfig();
    }
}
