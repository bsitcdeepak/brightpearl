<?php

namespace Bsitc\Brightpearl\Model;

class ProducturiFactory extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
   // protected $_objectManager;

    public $_objectManager;
    public $_storeManager;
    public $_scopeConfig;
    public $_logManager;
    public $_api;
    public $_date;
    public $_producturi;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Producturi $producturi
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_producturi      = $producturi;
        $this->_scopeConfig     = $scopeConfig;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Producturi', $arguments, false);
    }
    
    public function checkAlredyExits($resp)
    {
        $resp      = $resp;
        $producturi  = $this->create();
        $collections = $producturi->getCollection()->addFieldToFilter('url', $resp);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }
    
    
    /*Insert Price list API data in custom table bsitc_brightpearl_products_uri*/
    public function setProducturiApi()
    {
        if ($this->_api->authorisationToken) {
            $responses = $this->_api->fetchProducts();
            $producturi = [];
            foreach ($responses as $response) {
                $id_exit = $this->checkAlredyExits($response);
                if ($id_exit == 'true') {
                    continue;
                }
                            $producturi['url'] = $response;
                            $producturi['sync'] = 0;
                            $this->addRecord($producturi);
            }
        }
    }
    
    public function addRecord($data)
    {
        $producturidata  = $this->create();
        $producturidata->setData($data);
        $producturidata->save();
    }
}
