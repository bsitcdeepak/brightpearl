<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Products extends \Magento\Framework\App\Action\Action
{
    protected $productapi;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\BpproductsFactory $productapi
    ) {
        $this->productapi = $productapi;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->productapi->syncBpProductsByrangeApi();
    }
}
