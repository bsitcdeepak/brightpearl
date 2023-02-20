<?php

namespace Bsitc\Brightpearl\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_directoryList;
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_directoryList     = $directoryList;
        $this->_scopeConfig     = $context->getScopeConfig();
        $this->_objectManager      = $objectManager;
        $this->_storeManager      = $storeManager;
        parent::__construct($context);
    }

    public function getMediaPath()
    {
        return $this->_directoryList->getPath('media');
    }
    
    /**
     * Get store config
     */
    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
    
    /*used in Observers*/
    public function getBrightpearlEnable()
    {
        return $bpenable  =  $this->getConfig('bpconfiguration/api/enable');
    }
    
    /*show for custom shipping methods*/
    public function getWarehouseEnable()
    {
        return $bpenable  =  $this->getConfig('bpconfiguration/bpcollectfromstore/active');
    }


    /*used in Observers*/
    public function getOrderqueueEnable()
    {
        return $bpenable  =  $this->getConfig('bpconfiguration/bp_orderconfig/enable');
    }

    /*Ecomm Attribute code*/
    public function getEcommAttributeEnable()
    {
        $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/enable');
        if ($bpenable) {
            return $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/ecomm_attribute');
        }
    }

    public function getBrightpearl($store)
    {
        $apiObj = '';
          $bpConfigData  =  (array) $this->getConfig('bpconfiguration/api', $store);
        
        if (isset($bpConfigData['enable']) && isset($bpConfigData['bp_useremail']) && isset($bpConfigData['bp_password']) && isset($bpConfigData['bp_account_id']) && isset($bpConfigData['bp_dc_code']) && isset($bpConfigData['bp_api_version'])) {
            $apiObj    = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Api', ['data' => $bpConfigData]);
        }
        return $apiObj;
    }
}
