<?php

namespace Bsitc\Brightpearl\Model;

class PricelistconfigFactory extends \Magento\Framework\Model\AbstractModel
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
    public $_pricelist;
    public $_pricelistconfig;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Pricelistconfig $pricelistconfig,
        \Bsitc\Brightpearl\Model\Pricelist $pricelist
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_pricelist          = $pricelist;
        $this->_pricelistconfig = $pricelistconfig;
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Pricelistconfig', $arguments, false);
    }
    
    public function checkAlredyExits($id)
    {

        $productid      = $id;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('bp_id', $productid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }
    
    
    public function updateRecord($id, $row)
    {
        $record =  $this->create()->load($id);
         $record->setData($row);
        $record->setId($id);
        $record->save();
    }
    
    public function findRecord($column, $value)
    {
        
        $data = '';
        $collection = $this->create()->getCollection()->addFieldToFilter($column, $value);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
        }
        return $data;
    }
    
    /*Insert Price list API data in custom table bsitc_brightpearl_pricelist*/
    public function getAllPriceListConfig()
    {
        if ($this->_api->authorisationToken) {
            $data = $this->_api->getAllPriceList();
            $responses = $data['response'];
            foreach ($responses as $response) {
                 $pricedata                                 = [];
                 $pricedata['bp_id']                     = $response['id'];
                $pricedata['name']                         =  json_encode($response['name']);
                $pricedata['code']                         = $response['code'];
                $pricedata['currency_code']             = $response['currencyCode'];
                $pricedata['currency_symbol']             = $response['currencySymbol'];
                $pricedata['currency_id']                 = $response['currencyId'];
                $pricedata['pricelist_type_Code_id']     = $response['priceListTypeCode'];
                $pricedata['gross']                     = $response['gross'];
                $pricedata['sync']                         = 0;
                 $search = $this->findRecord('bp_id', $response['id']);
                
                if ($search) {
                    $id =  $search->getId();
                    $this->updateRecord($id, $pricedata);
                } else {
                    $this->addRecord($pricedata);
                }
            }
        }
    }
    
    

    
    public function addRecord($data)
    {
        $pricelistdata  = $this->create();
        $pricelistdata->setData($data);
        $pricelistdata->save();
    }
}
