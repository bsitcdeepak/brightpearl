<?php

namespace Bsitc\Brightpearl\Model;

class BrandFactory extends \Magento\Framework\Model\AbstractModel
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
    public $_brand;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Brand $brand
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_brand              = $brand;
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Brand', $arguments, false);
    }


    public function checkAlredyExits($id)
    {
        $brandid = $id;
        
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('bp_id', $brandid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }


    public function syncBrandApi()
    {
            
        if ($this->_api->authorisationToken) {
            $data = $this->_api->getProductBrand();
            $responses = $data['response'];
            /*echo '<pre>';
            print_r($responses);
            echo '</pre>';
            die;*/

            $branddata = [];
            foreach ($responses as $response) {
                    $id_exit = $this->checkAlredyExits($response['id']);
                if ($id_exit == 'true') {
                    continue;
                }

                    $branddata['bp_id'] = $response['id'];
                    $branddata['magento_id'] = '';
                    $branddata['name'] = $response['name'];
                    $branddata['description'] = $response['description'];
                    $branddata['sync'] = 0;
                    $this->addRecord($branddata);
            }
        }
    }
    
    public function addRecord($data)
    {
        $bpproducts  = $this->create();
        $bpproducts->setData($data);
        $bpproducts->save();
    }
}
