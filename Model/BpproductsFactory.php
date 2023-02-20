<?php

namespace Bsitc\Brightpearl\Model;

class BpproductsFactory extends \Magento\Framework\Model\AbstractModel
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
    public $_bpproducts;

    public $_producturi;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Bpproducts $bpproducts,
        \Bsitc\Brightpearl\Model\ProducturiFactory $producturi
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_bpproducts      = $bpproducts;
        $this->_scopeConfig     = $scopeConfig;
        $this->_producturi      = $producturi;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpproducts', $arguments, false);
    }

    
    public function CheckProductType($groupid)
    {
        $groupid      = $groupid;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('product_group_id', $groupid);
        $collections = $collections->getData();
        if (count($collections) == 1) {
                return 'false';
        } elseif (count($collections) > 1) {
                return 'true';
        }
    }

    
    public function checkAlredyExits($id)
    {
        $productid      = $id;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('product_id', $productid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }



    /*public function checkCustomAttribute($data){
            $collections = $data;
            $productids = array();
            $checkcustomattribute = $this->_api->getProductBySingleCustomField($custom_bp_id);
            if(is_array($collections)){
                foreach($collections as $collection){
                    if(is_array($collection)){
                        foreach ($collection as $key => $value) {
                            if(array_key_exists('PCF_ECOM_PRO', $value)){
                                foreach($value as $keys => $val)
                                {
                                    if($keys == 'PCF_ECOM_PRO'){
                                        if($val == 1){
                                            $productids[] =  $key;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            return $productids;
            }
    }*/

    public function checkCustomAttribute($data)
    {
    
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $checkcustomattribute = $this->_scopeConfig ->getValue('bpconfiguration/bpproduct/ecomm_attribute', $storeScope);
         $checkcustomattribute =    isset($checkcustomattribute) ? $checkcustomattribute : 'PCF_ECOM_PRO' ;
        $collections = $data;
        $productids = [];
        // $checkcustomattribute = $this->_api->getProductBySingleCustomField($custom_bp_id);
        if (is_array($collections)) {
            foreach ($collections as $collection) {
                if (is_array($collection)) {
                    foreach ($collection as $key => $value) {
                        if (array_key_exists($checkcustomattribute, $value)) {
                            foreach ($value as $keys => $val) {
                                if ($keys == $checkcustomattribute) {
                                    if ($val == 1) {
                                        $productids[] =  $key;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $productids;
        }
    }


    
    //public function syncBpProductsByrangeApi($range){
    
    public function syncBpProductsByrangeApi()
    {
    
        if ($this->_api->authorisationToken) {
             /*Fetching Uri's from uri's tables*/
             $collections = $this->_producturi->create()->getCollection();
            $collections->addFieldToFilter('url', ['notnull' => true]);
            //$collections->addFieldToFilter('url',['neq' => 'NULL']);
                
            foreach ($collections as $item) {
                 /*Check If has a custom Attribute*/
                $collection = str_replace("/product/", "", $item->getUrl());
                 $data = $this->_api->getProductByCustomFields($collection);
                                            
                /*Check custom Attributes*/
                $ids = [];
                if (array_key_exists('response', $data)) {
                    if (is_array($data)) {
                        $ids = $this->checkCustomAttribute($data);
                    }
                }
                if (count($ids) > 0) {
                     sort($ids);
                     $cols = implode(',', $ids);
                      /*For All products */
                      $data = $this->_api->getAllProductFromUriAttribute($cols);
                    if (array_key_exists('response', $data)) {
                        $responses = $data['response'];
                        $productdata = [];
                        if (is_array($responses)) {
                            foreach ($responses as $response) {
                                $id_exit = $this->checkAlredyExits($response['id']);
                                if ($id_exit == 'true') {
                                       continue;
                                }
                                 
                                   $productdata['product_id']             = $response['id'];
                                   $productdata['brand_id']             = $response['brandId'];
                                   $productdata['sku']                 = $response['identity']['sku'];
                                   $productdata['created_at']             = date('Y-m-d H:i:s');
                                   $productdata['type']                 = 'simple';
                                   $productdata['collection_id']         = '';
                                   $productdata['product_type_id']     = '';
                                   $productdata['featured']             = '';
                                   $productdata['isbn']                 = '';
                                   $productdata['upc']                 = '';
                                   $productdata['ean']                 = '';
                                   $productdata['mpc']                 = '';
                                   $productdata['barcode']             = '';
                                   $productdata['product_group_id']     = '';
                                   $productdata['updated_at']             = '';
                                   $productdata['variations']             = '';
                                   $productdata['season']                 = '';
                                    //$productdata['dimension'] = $response['dimensions'];
                                   $productdata['dimension']             = json_encode($response['stock']);
                                   $productdata['taxcode_id']             = $response['financialDetails']['taxCode']['id'];
                                   $productdata['taxcode_code']         = $response['financialDetails']['taxCode']['code'];
                                   $productdata['sales_channel_name']     = $response['salesChannels']['0']['salesChannelName'];
                                   $productdata['product_name']         = $response['salesChannels']['0']['productName'];
                                   $productdata['categories']             = json_encode($response['salesChannels']['0']['categories']);
                                   $productdata['description']         = $response['salesChannels']['0']['description']['text'];
                                   $productdata['short_description']     = $response['salesChannels']['0']['shortDescription']['text'];
                                   $productdata['condition']             = $response['salesChannels']['0']['productCondition'];
                                   $productdata['state']                 = 'new';
                                   $productdata['warehouse']             =  json_encode($response['warehouses']);
                                ;
                                   $productdata['nominal_purchase_stock'] = $response['nominalCodeStock'];
                                   $productdata['nominal_purchase_purchase'] = $response['nominalCodePurchases'];
                                   $productdata['nominal_purchase_sales'] = $response['nominalCodeSales'];
                                   $productdata['status']                 = $response['status'];
                                   $productdata['syc_status']             = 0;
                                   $productdata['queue_status']         = 'pending';
                                    
                                if (array_key_exists("collectionId", $response)) {
                                    $productdata['collection_id'] = $response['collectionId'];
                                }
                                if (array_key_exists("productTypeId", $response)) {
                                    $productdata['product_type_id'] = $response['productTypeId'];
                                }
                                if (array_key_exists("featured", $response)) {
                                    $productdata['featured'] = $response['featured'];
                                }
                                if (array_key_exists("isbn", $response['identity'])) {
                                    $productdata['isbn'] = $response['identity']['isbn'];
                                }
                                if (array_key_exists("upc", $response['identity'])) {
                                    $productdata['upc'] = $response['identity']['upc'];
                                }
                                if (array_key_exists("ean", $response['identity'])) {
                                    $productdata['ean'] = $response['identity']['ean'];
                                }
                                if (array_key_exists("mpn", $response['identity'])) {
                                    $productdata['mpc'] = $response['identity']['mpn'];
                                }
                                if (array_key_exists("barcode", $response['identity'])) {
                                    $productdata['barcode'] = $response['identity']['barcode'];
                                }
                                if (array_key_exists("productGroupId", $response)) {
                                    $productdata['product_group_id'] = $response['productGroupId'];
                                }
                                if (array_key_exists("createdOn", $response)) {
                                    $productdata['created_at'] = $response['createdOn'];
                                }
                                if (array_key_exists("updatedOn", $response)) {
                                    $productdata['updated_at'] = $response['updatedOn'];
                                }
                                if (array_key_exists("seasonIds", $response)) {
                                    $productdata['season'] = json_encode($response['seasonIds']);
                                }
                                if (array_key_exists("variations", $response)) {
                                    $productdata['variations'] = json_encode($response['variations']);
                                    if ($productdata['variations']) {
                                           /*Check If same groupidexits for simple and configurable products*/
                                           $check_name = $this->CheckProductType($response['productGroupId']);
                                        if ($check_name == 'true') {
                                            $productdata['type'] = 'simple';
                                        } else {
                                            $productdata['type'] = 'configurable';
                                        }
                                    } else {
                                           $productdata['type'] = 'simple';
                                    }
                                }
                                if ($productdata['sku']!="") {
                                    $this->addRecord($productdata);
                                }
                            }
                        }
                    }
                }
                usleep(2000000);  /* 2000000 microseconds = 2 seconds */
            }
        }
    }
    
    
      
    public function addRecord($row)
    {
        if (count($row)>0) {
            $record = $this->create();
            $record->setData($row);
            $record->save();
        }
        return true;
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

    public function removeAllRecord()
    {
        $collection = $this->create()->getCollection();
        $collection->walk('delete');
        return true;
    }
    
    public function removeRecord($id)
    {
        $record = $this->create()->load($id);
        if ($record) {
            $record->delete();
        }
        return true;
    }
}
