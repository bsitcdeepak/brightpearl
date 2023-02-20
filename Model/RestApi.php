<?php
/**
 * Brightpearl API
 *
 * Brightpearl Magento API class
 * @package brightpearl
 * @version 2.0
 * @author Vijay Vishvkarma
 * @email vijay1982.msc@gmail.com
 */

namespace Bsitc\Brightpearl\Model;

class RestApi extends \Magento\Framework\Model\AbstractModel
{
    public $apiUser;
    public $apiEmail;
    public $apiPassword;
    public $apiToken;
    public $ch;
    public $authorisationToken;
    public $restURL;
    public $enable;
    public $apiResponse;
    public $msgError;
    public $_scopeConfig;
    public $_storeManager;
    public $_objectManager;
    public $_logManager;
    public $_data;
    public $_categoryurl;
    public $_producturl;
    public $_bulkproducturl;
    public $_attributeurl;
    public $_api;
    public $_productcollections;
    public $_brandcollections;
    public $_attributecollections;
    public $_categorycollections;
    public $_customattcollections;
    public $_datahelper;
    public $_associateproduct;

    protected $_connection;
    protected $_resource;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\BpproductsFactory $productcollections,
        \Bsitc\Brightpearl\Model\BrandFactory $brandcollections,
        \Bsitc\Brightpearl\Model\AttributeFactory $attributecollections,
        \Bsitc\Brightpearl\Model\BpproductsFactory $productscollections,
        \Bsitc\Brightpearl\Model\CategoryFactory $categorycollections,
        \Bsitc\Brightpearl\Model\CustomattributeFactory $customattcollections,
        \Bsitc\Brightpearl\Model\AssociateproductFactory $associateproduct,
        \Bsitc\Brightpearl\Helper\Data $datahelper,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_objectManager         = $objectManager;
        $this->_storeManager           = $storeManager;
        $this->_scopeConfig            = $scopeConfig;
        $this->_api                       = $api;
        $this->_logManager           = $logManager;
        $this->_productcollections      = $productscollections;
        $this->_brandcollections     = $brandcollections;
        $this->_attributecollections = $attributecollections;
        $this->_categorycollections  = $categorycollections;
        $this->_datahelper              = $datahelper;
        $this->_customattcollections = $customattcollections;
        $this->_associateproduct     = $associateproduct;
        $this->_resource              = $resource;
        $this->_connection              = $this->_resource->getConnection();
        $this->getToken();
        $this->getConfiguration();
    }

    protected function getConfiguration()
    {
        $this->ch                 = curl_init();
        $storeScope               = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $data['productenable']           = $this->_scopeConfig->getValue('bpconfiguration/bpproduct/enable', $storeScope);
        $data['priceconfig']      = $this->_scopeConfig->getValue('bpconfiguration/bpproduct/bp_pricelist', $storeScope);
        $data['sppriceconfig']      = $this->_scopeConfig->getValue('bpconfiguration/bpproduct/bp_sppricelist', $storeScope);
        $this->_data              = $data;
    }
    
    /*Get Base Urls*/
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
 
    /*Common function to hits the Magento API*/
    public function setCategoryApiFinal()
    {
        
        $resultPage = $this->_categorycollections->create();
        $collections = $resultPage->getCollection();
        $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
        
        foreach ($collections as $collection) {
            $id = $collection['id'];
            $catname = $collection['name'];
            $cate = $category->getCollection()->addAttributeToFilter('name', $catname)->getFirstItem();

            if ($cate->getId()) {
                $mg_cat_id =$cate->getId();
                $catcol = $resultPage->load($id);
                $catcol->setMgCategoryId($mg_cat_id);
                $catcol->save();
                continue;
            }

            $name=ucfirst($cat);
            $url=strtolower($cat);
            $cleanurl = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($url))))));
            $categoryFactory = $this->_objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            /// Add a new sub category under root category
            $categoryTmp = $categoryFactory->create();
            $categoryTmp->setName($name);
            $categoryTmp->setIsActive(true);
            $categoryTmp->setUrlKey($cleanurl);
            $categoryTmp->setParentId(2);
            $categoryTmp->setStoreId($storeId);
            $categoryTmp->setPath($rootCat->getPath());
            $categoryTmp->save();
        }
    }
    


     /*Common function to hits the Magento API*/
    public function setCategoryApi()
    {

        $resultPage = $this->_categorycollections->create();
        $collections = $resultPage->getCollection()->addFieldToFilter('sync', 0);
        $category = $this->_objectManager->create('Magento\Catalog\Model\Category');

        $cat_info = $category->load(2);
        $path = $category->getPath();

        /*Table name for inserts*/
        $catalog_category_entity = $catalog_product_entity = $this->_resource->getTableName('catalog_category_entity');

        $catalog_category_entity_varchar = $this->_resource->getTableName('catalog_category_entity_varchar');
        
        $catalog_category_entity_int = $this->_resource->getTableName('catalog_category_entity_int');
        $sequence_catalog_category = $this->_resource->getTableName('sequence_catalog_category');

        foreach ($collections as $collection) {
            $id = $collection['id'];
            $name = ucfirst($collection['name']);
            $url  = strtolower($name);

            if ($collection['parentId'] == 0) {
                $cat_info = $category->load(2);
                $path = $category->getPath();
            } else {
                $catcol = $resultPage->load($id);
                $mgtid = $catcol->getMgCategoryId();
                //$cat_info = $category->load($mgtid);
                $path = $category->getPath().'/'.$mgtid;
            }

            if ($collection['mg_category_id']) {
                $this->_logManager->recordLog(json_encode($collection['mg_category_id'], true), "Category already exits", "Category");
                continue;
            }

            $cate = $category->getCollection()->addAttributeToFilter('name', $name)->getFirstItem()->getId();
            if ($cate) {
                $catcol = $resultPage->load($id);
                $catcol->setMgCategoryId($cate);
                $catcol->setSync(1);
                $catcol->save();
                continue;
            }

            $cleanurl = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($url))))));
            
            /*set id for category*/

            $sequence_id = $this->InsertSequenceCatalogCategory();
            $this->_connection->insertMultiple($sequence_catalog_category, $sequence_id);
            $entity_id = $this->_connection->lastInsertId();

            

            if ($entity_id) {
                /*Insret into catalog_category_entity_tables*/
                $new_path = $path.'/'.$entity_id;
                $insertentitydata = $this->InsertCatalogCategoryEntity($entity_id, $new_path);
                $this->_connection->insertMultiple($catalog_category_entity, $insertentitydata);
                $row_id = $this->_connection->lastInsertId();

                if ($row_id) {
                /*url name*/
                    $catnamedata = $this->InsertCatalogCategoryVarchar(42, 0, $name, $row_id);
                    $this->_connection->insertMultiple($catalog_category_entity_varchar, $catnamedata);
                /*url keys*/
                    $caturldata = $this->InsertCatalogCategoryVarchar(120, 0, $cleanurl, $row_id);
                    $this->_connection->insertMultiple($catalog_category_entity_varchar, $caturldata);

                /*Include in menu*/
                    $includeinmenudata  = $this->InsertCatalogCategoryEntityInt(66, 0, 1, $row_id);
                    $this->_connection->insertMultiple($catalog_category_entity_int, $includeinmenudata);
                /*Set is active*/
                    $includeinmenudata = $this->InsertCatalogCategoryEntityInt(43, 0, 0, $row_id);
                    $this->_connection->insertMultiple($catalog_category_entity_int, $includeinmenudata);
                    $mg_cat_id = $entity_id;
                    $catcol = $resultPage->load($id);
                    $catcol->setMgCategoryId($mg_cat_id);
                    $catcol->setSync(1);
                    $catcol->save();
                    $this->_logManager->recordLog(json_encode($entity_id, true), "Category :".$entity_id, "Category");
                }
            }
        }
        //$this->UpdateParentsCategory();
    }

    

    /*Array for Insert Queries*/
    public function InsertSequenceCatalogCategory()
    {
        return $data[] = [
                            'sequence_value'                        =>     'Null'
                        ];
    }


    /*Array for Insert Queries*/
    public function InsertCatalogCategoryEntity($entity_id, $path)
    {
        return $data[] = [
                    'row_id'                        =>     'Null',
                    'entity_id'                        =>     $entity_id,
                    'created_in'                    =>     1,
                    'updated_in'                    =>     2147483647,
                    'attribute_set_id'                =>     3,
                    'parent_id'                        =>     2,
                    'path'                            =>     $path,
                    'position'                        =>     1,
                    'level'                            =>     2,
                    'children_count'                =>     0
            ];
    }

    /*Array for Insert Queries  attribute id 42(name), url_key (120)*/
    public function InsertCatalogCategoryVarchar($attributeid, $store_id, $value, $rowid)
    {
        return $data[] = [
                    'attribute_id'                    =>     $attributeid,
                    'store_id'                        =>     $store_id,
                    'value'                            =>     $value,
                    'row_id'                        =>     $rowid
            ];
    }

    /*Inlcude in menu 66, 51 is_anchore, 43 is_active*/
    public function InsertCatalogCategoryEntityInt($attributeid, $store_id, $value, $rowid)
    {
        return $data[] = [
                    'attribute_id'                    =>     $attributeid,
                    'store_id'                        =>     $store_id,
                    'value'                            =>     $value,
                    'row_id'                        =>     $rowid
            ];
    }

    /*Update queue systems*/
    public function ProductQueue($bpid, $status)
    {
            $status = $status;
            $bpid = $bpid;
            $collections = $this->_productcollections->create()->getCollection();
            $collections = $collections->addFieldToFilter('product_id', $bpid);
        foreach ($collections as $collection) {
            $id = $collection->getId();
            $cols = $collection->load($id);
            $datas = $cols->setQueueStatus($status);
            $cols->save();
        }
    }

    /*Create Porducts */
    public function setProductsApi()
    {
        $this->getConfiguration();
        $resultPage = $this->_productcollections->create();
        $collections = $resultPage->getCollection()->addFieldToFilter('queue_status', 'pending');
        $allcol = [];
        $alldata = [];
        $response = '';
        foreach ($collections as $collection) {
                $collectionconf = $collection;
                $collectionsimple = $collection;
                $bp_id = $collection['product_id'];
                $response = '';
                /*We need to create 2 products with same rows so check condition two times simple and configurable*/
            if ($collection->getType() == 'configurable') {
                    /*If Magento Products are exits*/
                    $magentoid = $collection['magento_id'];
                if ($magentoid == "") {
                    $data = $this->_datahelper->CreateProducts($collection, 'simple');
                    $this->_logManager->recordLog(json_encode($data, true), "Product Id", "Success");
                    $response = $this->SetMagentoProductId($data, $collection, "simple");
                }

                    //sleep(1);
                        
                    $data = $this->_datahelper->CreateProducts($collection, 'configurable');
                    $this->_logManager->recordLog(json_encode($data, true), "Product Condfigurable", "Success");
                    $response  = $this->SetMagentoProductId($data, $collection, "configurable");
            }

            if ($collection->getType() == 'simple') {
                /*Create a new products in Magento*/
                $data = $this->_datahelper->CreateProducts($collection, 'simple');
                $this->_logManager->recordLog(json_encode($data, true), "Product Id", "Success");
                $response = $this->SetMagentoProductId($data, $collection, "simple");
                $this->_logManager->recordLog(json_encode($response, true), "Product check 2 ", "Product check");
            }
                

            if ($response == "true") {
                $status = 'complete';
                $this->ProductQueue($bp_id, $status);
            } elseif ($response == "false") {
                $status = 'error';
                $this->ProductQueue($bp_id, $status);
            } else {
                $status = 'complete';
                $this->ProductQueue($bp_id, $status);
            }
        }
        return json_decode($response, true);
    }


    /*Set Products Id in local tables*/
    public function SetMagentoProductId($mgtid, $collection, $type)
    {
        $result = 'false';
        $resultPage = $this->_productcollections->create();
        $bp_id = $collection['product_id'];
                            
        if ($mgtid == "custom_product_updated") {
                $status = 'complete';
                $this->ProductQueue($bp_id, $status);
                $result = "true";
        } else {
            if ($mgtid) {
                $id = $collection['id'];
                $catcol = $resultPage->load($id);
                if ($type == 'simple') {
                    $catcol->setMagentoId($mgtid);
                    $res = $this->setBarcodeMagestore($collection, $mgtid);
                } else {
                    $catcol->setConfProId($mgtid);
                }
                $catcol->save();
                if ($mgtid) {
                    $status = 'complete';
                    $this->ProductQueue($bp_id, $status);
                    $result = "true";
                }
            }
        }
        return $result;
    }
            

    
        /*Update Barcode for Magestore Module*/
    public function setBarcodeMagestore($collection, $mg_pd_id)
    {
        /*If Magestore Module are enables*/
        if ($this->_datahelper->getMagestoreBarcodeEnable()) {
             $history          = $this->_objectManager->create('Magestore\BarcodeSuccess\Api\Data\HistoryInterface');
            $historyResource = $this->_objectManager->create('Magestore\BarcodeSuccess\Model\ResourceModel\History');
            $totalQty = 1;
            /*Start Genereate Barcode*/
            if ($collection['ean']) {
                $adminuserid = $this->getAdminUser();
                if ($adminuserid) {
                        $history->setData('type', $adminuserid);
                        $history->setData('reason', '');
                        $history->setData('created_by', $adminuserid);
                        $history->setData('total_qty', $totalQty);
                        $historyResource->save($history);
                        $historyId =  $history->getId();
                    if ($historyId) {
                        $barcodeObj = $this->_objectManager->create('\Magestore\BarcodeSuccess\Model\Barcode');
                        $data = [];
                        $data['barcode'] = $collection['ean'];
                        $data['qty'] = '1';
                        $data['product_id'] = $mg_pd_id;
                        $data['product_sku'] = $collection['sku'];
                        $data['supplier_id'] = '0';
                        $data['supplier_code'] = '';
                        $data['purchased_id'] = '0';
                        $data['purchased_time'] = '';
                        $data['history_id'] = $historyId;
                         $this->_logManager->recordLog(json_encode($data, true), "Product barcode ", "Product");
                         $barcodeObj->setData($data);
                        $barcodeObj->save();
                    }
                }
            }
            /*End Code for barcode*/
        }
    }


    public function getAdminUser()
    {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $adminUsers = $objectManager->get('\Magento\User\Model\ResourceModel\User\Collection')->getData();
            $data = '';
        foreach ($adminUsers as $user) {
            $data = $user['user_id'];
            break;
        }
        return $data;
    }

    /*
        Set Attribute for Dropdown types By Mick
    */
    public function setBrandOptions($attcode)
    {
        $code = $attcode;
        /*load collection from brand tables*/
        $resultPage = $this->_brandcollections->create();
        $collections = $resultPage->getCollection();
        foreach ($collections as $collection) {
            $label = $collection->getName();
            $id = $collection->getId();
            /*Call a helper for create options*/
            $option_id = $this->_datahelper->createAttributeOptions($code, $label);
            $data = $resultPage->load($id);
            $data->setMagentoId($option_id);
            $data->save();
        }
    }
    

     /*
        Set Attribute for Dropdown Collection types By Mick
    */
    //public function setCollectionOption($attcode)
    public function setCollectionOption()
    {
         /*$code = $attcode;
          $bpcode = '';
          $mgtcode = '';

          $allcodes = explode(":", $code);
          if(array_key_exists(0, $allcodes)){
                $bpcode = trim($allcodes[0]);
          }

          if(array_key_exists(1, $allcodes)){
                $mgtcode = trim($allcodes[1]);
          }*/

        /*load collection from brand tables*/
        $resultPage = $this->_customattcollections->create();
        $collections = $resultPage->getCollection();
        foreach ($collections as $collection) {
            //$label = $collection->getCollectionName();
            $codes = $collection->getMgtCode();
            $label = $collection->getCustomData();
            $id = $collection->getId();
            $data = $resultPage->load($id);
            //$option_id = $this->_datahelper->createAttributeOptions($mgtcode, $label);
            $option_id = $this->_datahelper->createAttributeOptions($codes, $label);
            //$data->setCustomData($option_id);
            $data->setOptionValueId($option_id);
            $data->save();
        }
    }
    
    
    
     /*
        Set Attribute for Colour and Size
    */
    public function setAttributeOptions()
    {
        /*load collection from options tables*/
        $resultPage  = $this->_attributecollections->create();
        $collections = $resultPage->getCollection();
        $bpcolour   = $this->_datahelper->getBpColour();
        $bpsize     = $this->_datahelper->getBpSize();
        $mgtcolour  = $this->_datahelper->getColour();
        $mgtsize    = $this->_datahelper->getSize();
        foreach ($collections as $collection) {
            $data = $collection->getAttrCode();
            //if(($data == $mgtsize) || ($data == $mgtcolour)){
            //if($collection->getAttrCode()){
            if (($collection->getAttrCode()) || ($collection->getOptionValueName()) && (($collection->getMgtCode()))) {
                //$code = $collection->getAttrCode();
                $code = $collection->getMgtCode();
                $label = $collection->getOptionValueName();
                $id = $collection->getId();
                //---- Call a helper for create options
                $option_id = $this->_datahelper->createAttributeOptions($code, $label);
                $data = $resultPage->load($id);
                $data->setMgOptionValueId($option_id);
                $data->setMgtCode($code);
                $data->save();
            }
            //}
        }
    }

    public function setSeasonOptions()
    {
        /*load collection from options tables*/
        $resultPage  = $this->_attributecollections->create();
        $collections = $resultPage->getCollection();
        $collections = $resultPage->getCollection()->addFieldToFilter('attr_code', 'season');
        if ($collections->getSize()) {
            foreach ($collections as $collection) {
                if (($collection->getAttrCode()) || ($collection->getOptionValueName())) {
                    $code = $collection->getAttrCode();
                    $label = $collection->getOptionValueName();
                    $id = $collection->getId();
                    $option_id = $this->_datahelper->createAttributeOptions($code, $label);
                    $data = $resultPage->load($id);
                    $data->setMgOptionValueId($option_id);
                    $data->save();
                }
            }
        }
    }

    public function setBpSeasonOptions()
    {
        /*load collection from options tables*/
        $resultPage  = $this->_attributecollections->create();
        $collections = $resultPage->getCollection();
       
        foreach ($collections as $collection) {
            if (($collection->getAttrCode() == 'season')) {
                if (($collection->getAttrCode()) || ($collection->getOptionValueName())) {
                    $code = $collection->getAttrCode();
                    $label = $collection->getOptionValueName();
                    $id = $collection->getId();
                    $option_id = $this->_datahelper->createAttributeOptions($code, $label);
                    $data = $resultPage->load($id);
                    $data->setMgOptionValueId($option_id);
                    $data->save();
                }
            }
        }
    }

    
    public function recordLog($log_data, $title = "M2 Rest API")
    {
        $logArray             = [];
        $logArray['category'] = 'Global';
        $logArray['title']    = $title;
        $logArray['store_id'] = 1;
        $logArray['error']    = json_encode($log_data, true);
        $this->_logManager->addLog($logArray);
        return true;
    }
    
    
    public function setCommonCurlRequest($url, $method, $data, $json = true, $callTitle = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeader());
        if ($data && $data != "") {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $response   = $this->executeQuery($ch);
        $errorCheck = $this->checkErrorsInCall($response, $callTitle, $ch);
        if ($errorCheck) {
            $response = $this->executeQuery($ch);
        }
        $this->apiResponse = $response;
        return $response;
    }
    
    public function executeQuery($ch)
    {
        $response = curl_exec($ch);
        return $response;
    }
    
    public function checkErrorsInCall($response, $callTitle, $ch)
    {
        
        $responseCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (false === $response || $responseCode != '200') {
            $errmsg         = curl_error($ch);
            $this->apiError = json_encode($errmsg, true);
            if ($errmsg) {
                $this->recordLog($errmsg, 'API Error');
            }
            $this->recordLog($response, 'API Response' . $callTitle);
            return true;
        }
        return false;
    }
}
