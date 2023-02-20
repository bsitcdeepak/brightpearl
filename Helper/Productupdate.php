<?php

namespace Bsitc\Brightpearl\Helper;

class Productupdate extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_directoryList;
    
    protected $_scopeConfig;
    
    protected $_objectManager;
    
    protected $_storeManager;

    protected $_bpsalescredit;

    protected $attributeRepository;
    
    protected $attributeValues;
        
    protected $attributeOptionManagement;
    
    protected $optionLabelFactory;
    
    protected $optionFactory;

    protected $_pricelist;

    protected $_categorycollection;

    protected $_brandcollection;

    protected $_collectionattr;

    protected $_attributedata;
    
    
    protected $associatedproduct;
    
    protected $moduleManager;
    
    protected $_warehouseFactory;
    
    
    protected $_webhookinventory;
    
    protected $_api;

    protected $_bpproductsFactory;
    
    protected $_logManager;

    protected $_webhookupdate;
    
    protected $productResourceModel;
    protected $productFactory;
    
    
    protected $_date;
    
    

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Bsitc\Brightpearl\Model\PricelistFactory $pricelist,
        \Bsitc\Brightpearl\Model\BpsalescreditFactory $bpsalescredit,
        \Bsitc\Brightpearl\Model\CategoryFactory $categorycollection,
        \Bsitc\Brightpearl\Model\BrandFactory $brandcollection,
        \Bsitc\Brightpearl\Model\CustomattributeFactory $collectionattr,
        \Bsitc\Brightpearl\Model\AttributeFactory $attributedata,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\AssociateproductFactory $associatedproduct,
        \Bsitc\Brightpearl\Model\BpproductsFactory $bpproductsFactory,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\WarehouseFactory $warehouseFactory,
        \Bsitc\Brightpearl\Model\WebhookinventoryFactory $webhookinventory,
        \Bsitc\Brightpearl\Model\WebhookupdateFactory $webhookupdate,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
    ) {
        
        $this->_directoryList         = $directoryList;
        $this->_scopeConfig         = $context->getScopeConfig();
        $this->_objectManager          = $objectManager;
        $this->_storeManager          = $storeManager;
        $this->attributeRepository  = $attributeRepository;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory     = $optionLabelFactory;
        $this->optionFactory         = $optionFactory;
        $this->_pricelist             = $pricelist;
        $this->_categorycollection     = $categorycollection;
        $this->_bpsalescredit          =  $bpsalescredit;
        $this->_brandcollection      =  $brandcollection;
        $this->_collectionattr         = $collectionattr;
        $this->_attributedata         = $attributedata;
        $this->productFactory         = $productFactory;
        $this->associatedproduct     = $associatedproduct;
        $this->moduleManager         = $moduleManager;
        $this->_warehouseFactory   = $warehouseFactory;
        $this->_webhookinventory   = $webhookinventory;
        $this->_bpproductsFactory   = $bpproductsFactory;
        $this->_logManager             = $logManager;
        $this->_api                  = $api;
        $this->_webhookupdate        = $webhookupdate;
        $this->productResourceModel = $productResourceModel;
        $this->_date                 = $date;
        
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
    
    /*Check for Ecomm Attribute Status*/
    public function getEcommAttribute()
    {
        return $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/ecomm_attribute');
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
    
    
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpproducts', $arguments, false);
    }
    


    public function createpricelist(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Pricelist', $arguments, false);
    }
    

    public function checkAlredyExits($productid)
    {
         $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('product_id', $productid);
        if ($collections->getSize()) {
            return 'true';
        } else {
            return '';
        }
    }

    public function WebhookQueue($bpid, $status)
    {
            $status = $status;
            $bpid = $bpid;
            $collections = $this->_webhookupdate->create()->getCollection();
            $collections = $collections->addFieldToFilter('bp_id', $bpid);
        foreach ($collections as $collection) {
            $id = $collection->getId();
            $cols = $collection->load($id);
            $datas = $cols->setStatus($status);
            $cols->save();
        }
    }

    /*Brightpearl Product Create*/
    public function ProductupdateSync()
    {
    
        $this->cleanProcesQueue(); // ----- remove complete and not paid satus record from queue
        
        $this->updateStuckQueueRecord();  // ------- update stuck queue records
        
        if ($this->checkQueueProcessingStatus()) { // ------- check previous cron running or not
            return true;
        }
    
        $collections =  $this->_webhookupdate->create()->getCollection();
        $collections->addFieldToFilter('status', ['eq'=>'pending']);
        if (count($collections) > 0) {
            $allcollections  = $collections->getData();
            
            /* --------  update state in processing state  ---------------*/
            foreach ($collections as $item) {
                $item->setStatus('processing')->save();
            }
             
             /*Check for Ecomm Attributes*/
            $ecomm_attribute = trim($this->getEcommAttribute());
            //$this->_logManager->recordLog('TEST-1', "TEST", "TEST");

            foreach ($allcollections as $collection) {
                //$this->_logManager->recordLog('TEST-2', "TEST", "TEST");
            
                try {
                    $bp_id        = $collection['bp_id'];
                     $flag         = false;
                    /*Start check custom attributes for PCF_ECOM_PRO*/
                    $checkcustomattribute = $this->_api->getProductBySingleCustomField($bp_id);

                    if ($checkcustomattribute['response']) {
                        $customdatas = $checkcustomattribute['response'];
                        foreach ($customdatas as $key => $value) {
                            if (array_key_exists($ecomm_attribute, $value)) {
                                foreach ($value as $keys => $val) {
                                    if ($keys == $ecomm_attribute) {
                                        if ($val == 1) {
                                            $flag = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
 
                    if ($flag == false) {
                         $this->WebhookQueue($bp_id, 'error_no_ecomm_pro');
                        continue;
                    }
                    //$this->_logManager->recordLog('TEST-3', "TEST", "TEST");
                    /*Ends check custom attributes for PCF_ECOM_PRO*/
 
                    $data = $this->_api->getProductById($bp_id);
                    $responses = $data['response'];

                    $pricedata = $this->_api->getProductPriceList($bp_id);
                    $priceresponses = $pricedata['response'];
                     
                    if (is_array($responses)) {
                        foreach ($responses as $response) {
                            $id_exit = $this->checkAlredyExits($response['id']);
                            if ($id_exit == 'true') {
                                /*Call Function to update products data*/
                                $type = 'updated';
                                $this->UpdateProductsData($response, $response['id'], $type, $checkcustomattribute);
                                   /*update Pricelist tables*/
                                if ($priceresponses) {
                                    $this->UpdateProductPrice($priceresponses, $response['id']);
                                }
                                  $this->WebhookQueue($bp_id, 'complete'); /* Update status of queue */
                            } else {
                                /*Add data in new Rows*/
                                $type = 'insert';
                                $productdata = $this->InsertProduct($response, $type, $checkcustomattribute);
                                if ($productdata) {
                                    $this->addRecord($productdata);
                                }

                                /*Add price list at tables*/
                                if ($priceresponses) {
                                    $pricedata = $this->InsertProductPrice($priceresponses);
                                    if ($pricedata) {
                                        $this->addPriceRecord($pricedata);
                                    }
                                }
                                $this->WebhookQueue($bp_id, 'complete'); /* Update status of queue */
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->_logManager->recordLog(json_encode($e->getMessage(), true), "Product Webhooks", "Product Webhooks");
                }
            }
        }
    }
    
    /*Product price updates*/
    public function UpdateProductPriceBysync()
    {

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            //$priceconfig = $this->scopeConfig->getValue("bpconfiguration/api/bp_pricelist", $storeScope);
            $priceconfig = $this->scopeConfig->getValue("bpconfiguration/bpproduct/bp_pricelist", $storeScope);
    
            $obj = $this->_pricelist->create();
            $priceupdate  = $obj->getCollection()->addFieldToFilter('queue_status', 'pending');
            $priceupdates = $priceupdate->getData();
            $updatedprice = '';
            
        foreach ($priceupdates as $priceupdate) {
            $bpid          = $priceupdate['bp_product_id'];
            $pricecolls     = json_decode($priceupdate['bp_pricelist']);
            $pids = $this->getMgtproductId($bpid);
            foreach ($pricecolls as $pricecoll) {
                if ($pricecoll->priceListId == $priceconfig) {
                    $d = $pricecoll->quantityPrice;
                    foreach ($d as $prop) {
                             $updatedprice = $prop;
                        foreach ($pids as $pid) {
                            $this->setProductPrices($updatedprice, $pid);
                        }
                    }
                }
            }
        }
            //return $baseprice;
    }


    public function setProductPrices($Product_updatedprice, $pro_pids)
    {
         $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $associate = $resource->getTableName('catalog_product_entity_decimal');
        if ($pro_pids) {
            if ($Product_updatedprice) {
                $updateAttributeArray = ['value' => $Product_updatedprice ];
                $where = ['entity_id = ?' => $pro_pids, 'attribute_id = ?' => '77' ];
                $connection->update($associate, $updateAttributeArray, $where);
            }
        }
    }
    
    
    public function getMgtproductId($bpid)
    {
        $p_id = $bpid;
        $obj = $this->_bpproductsFactory->create();
        $productpricelists = $obj->getCollection()->addFieldToFilter('product_id', $p_id);
        $confpro_id = [];
        foreach ($productpricelists as $productpricelist) {
                    $confpro_id[] = $productpricelist['conf_pro_id'];
                    $confpro_id[] = $productpricelist['magento_id'];
        }
        return $confpro_id;
    }


    
    public function UpdateProductPrice($pricelistresponse, $p_id)
    {
        $priceobj = $this->_pricelist->create();
        $productpricelists = $priceobj->getCollection()->addFieldToFilter('bp_product_id', $p_id);
        $productpricelists = $productpricelists->getData();
            
        foreach ($productpricelists as $productpricelist) {
                $id = $productpricelist['id'];
                $setdata = $priceobj->load($id);
                $data = $this->InsertProductPrice($pricelistresponse);
                $setdata->setData($data);
                $setdata->setId($id);
                $setdata->save();
        }
    }
    
    
    public function InsertProductPrice($pricelistresponse)
    {
            $pricedata =  [];
            $response = $pricelistresponse;
        foreach ($response as $resp) {
                $pricedata['bp_product_id'] = $resp['productId'];
                $pricedata['bp_pricelist'] = json_encode($resp['priceLists']);
                $pricedata['mg_product_id'] = '';
                $pricedata['sync'] = 0;
                $pricedata['queue_status'] = 'pending';
        }
            return $pricedata;
    }
    
    
    
    public function UpdateProductsData($response, $pid, $type, $customfield)
    {
        $update_product_enable  =  $this->getConfig('bpconfiguration/bpproduct/update_product_enable');
        if ($update_product_enable) {
            $data = $this->InsertProduct($response, $type, $customfield);
             
            $collection = $this->create()->getCollection()->addFieldToFilter('product_id', $pid);
            if ($collection->getSize()) {
                $item  = $collection->getFirstItem();
                 $id = $item->getId();
                  $record = $this->create()->load($id);
                   $record->setData($data);
                $record->setId($id);
                $record->save();
            }
        }
         return true;
    }

    /*Check products types*/
    public function CheckProductType($groupid, $bp_id)
    {
        $groupid      = $groupid;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('product_group_id', $groupid);
        //$collections = $collections->getData();
        $data = '';
        if (count($collections)) {
            $product_arrays = [];
            $collections = $collections->getData();
            $product_arrays = [];
            foreach ($collections as $collection) {
                $pid = $collection['product_id'];
                $type = $collection['type'];
                $product_arrays[$pid] = $pid;
                $product_arrays[$pid] = $type;
            }
            /*Check if one is configurable*/
            if (in_array("configurable", $product_arrays)) {
                $data = 'simple';
            } else {
                $data = 'configurable';
            }
        } else {
            $data = 'configurable';
        }
        return $data;
    }


    public function CheckProductTypeSimple($groupid, $bp_id)
    {
        $groupid      = $groupid;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('product_group_id', $groupid);
        $data = '';
        if (count($collections)) {
            $collections = $collections->getData();
            foreach ($collections as $collection) {
                $pid = $collection['product_id'];
                $type = $collection['type'];
                $product_arrays[$pid] = $pid;
                $product_arrays[$pid] = $type;
            }

            $i = 0;
            foreach ($product_arrays as $key => $value) {
                if ($i == 0) {
                    $this->_logManager->recordLog(json_encode($i.' '.$key.' '.$bp_id, true), "Count : ".$bp_id, "Count");
                    if ($key == $bp_id) {
                            $data = 'configurable';
                            break;
                    }
                } else {
                            $data = 'simple';
                }
                $i++;
            }
        }
        //$this->_logManager->recordLog(json_encode($data, true), "Final Pass : ".$bp_id, "Final Pass");
        return $data;
    }

    
    /*Insret Records in Custom tables*/
    public function InsertProduct($response, $type, $customfield)
    {
        $productdata = [];
        $productdata['product_id']= '';
        $productdata['brand_id']= '';
        $productdata['collection_id']= '';
        $productdata['product_type_id'] = '';
        $productdata['featured'] = '';
        $productdata['sku'] = '';
        $productdata['isbn'] = '';
        $productdata['upc'] = '';
        $productdata['ean'] = '';
        $productdata['mpc'] = '';
        $productdata['barcode'] = '';
        $productdata['product_group_id'] = '';
        $productdata['created_at'] = date('Y-m-d H:i:s');
        $productdata['updated_at'] = '';
        $productdata['variations'] = '';
        $productdata['season'] = '';
        
        
        $productdata['product_id'] = $response['id'];
        $productdata['brand_id'] = $response['brandId'];
        $productdata['sku'] = $response['identity']['sku'];
        $productdata['dimension'] = json_encode($response['stock']);
        $productdata['taxcode_id'] = $response['financialDetails']['taxCode']['id'];
        $productdata['taxcode_code'] = $response['financialDetails']['taxCode']['code'];
        $productdata['sales_channel_name'] = $response['salesChannels']['0']['salesChannelName'];
        $productdata['product_name'] = $response['salesChannels']['0']['productName'];
        $productdata['categories'] = json_encode($response['salesChannels']['0']['categories']);
        $productdata['description'] = $response['salesChannels']['0']['description']['text'];
        $productdata['short_description'] = $response['salesChannels']['0']['shortDescription']['text'];
        $productdata['condition'] = $response['salesChannels']['0']['productCondition'];
        
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
        
        
        /*Check for simple and configurable products*/
        if (array_key_exists("variations", $response)) {
                $productdata['variations'] = json_encode($response['variations']);
            if ($productdata['variations']) {
                /*Check products types*/
                if ($type == 'insert') {
                    $check_name = $this->CheckProductType($response['productGroupId'], $response['id']);
                    if ($check_name == 'simple') {
                        $productdata['type'] = 'simple';
                    } else {
                        $productdata['type'] = 'configurable';
                    }
                } else {
                    $check_name = $this->CheckProductTypeSimple($response['productGroupId'], $response['id']);
                    if ($check_name == 'simple') {
                        $productdata['type'] = 'simple';
                    } else {
                        $productdata['type'] = 'configurable';
                    }
                }
            } else {
                    $productdata['type'] = 'simple';
            }
        } else {
            $productdata['type'] = 'simple';
        }

        $productdata['state'] = 'new';
        $productdata['queue_status'] = 'pending';
        $productdata['warehouse'] =  json_encode($response['warehouses']);
        ;
        $productdata['nominal_purchase_stock'] = $response['nominalCodeStock'];
        $productdata['nominal_purchase_purchase'] = $response['nominalCodePurchases'];
        $productdata['nominal_purchase_sales'] = $response['nominalCodeSales'];
        $productdata['status'] = $response['status'];
        $productdata['custom_field'] = json_encode($customfield);
        $productdata['syc_status'] = 0;
        return $productdata;
    }
        
    public function addRecord($data)
    {
        $update_bpproducts  = $this->create();
        $update_bpproducts->setData($data);
        $update_bpproducts->save();
    }
    
    
    public function addPriceRecord($data)
    {
        $update_bpproducts  = $this->_pricelist->create();
        $update_bpproducts->setData($data);
        $update_bpproducts->save();
    }
    
    
    
    public function updateStuckQueueRecord()
    {
            $adminHours = 0;
        if (!$adminHours) {
            $adminHours = 2;
        }
          $collection = $this->_webhookupdate->create()->getCollection();
          $collection->addFieldToFilter('status', ['eq'=>'processing']);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $date_a = $this->_date->date($item->getUpdatedAt());
                $date_b = $this->_date->date();
                $diff = $date_a->diff($date_b)->format('%i');
                if ($diff >= $adminHours) {
                    $item->setStatus('pending')->save();
                }
            }
        }
          return true;
    }

    public function checkQueueProcessingStatus()
    {
        $collection = $this->_webhookupdate->create()->getCollection();
        $collection->addFieldToFilter('status', [ 'eq'=>'processing' ]);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function cleanProcesQueue()
    {
        
        $collection = $this->_webhookupdate->create()->getCollection();
        $collection->addFieldToFilter('status', ['in' => ['complete','error_no_ecomm_pro']]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $item->delete();
            }
        }
    }
}
