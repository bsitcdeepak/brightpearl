<?php

namespace Bsitc\Brightpearl\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class WarehouseStoreConfigProvider implements ConfigProviderInterface
{

    protected $storeManager;

    protected $scopeConfig;
    
    protected $Allbpwarehouse;
    

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $Allbpwarehouse
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->allbpwarehouse = $Allbpwarehouse;
    }

    public function getConfig()
    {
        $store = $this->getStoreId();

        $config = [
            'shipping' => [
                'warehouse_store' => [
                    'customOptionValue' => $this->getCustomOptionValue()
                ]
            ]
        ];
        return $config;
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    public function getCustomOptionValue()
    {
        /*return [
            'option1' => 'Option 1',
            'option2' => 'Option 2',
            'option3' => 'Option 3'
        ];
        */
        
        return $this->allbpwarehouse->getSatus();
    }
}
