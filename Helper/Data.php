<?php

namespace Bsitc\Brightpearl\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_directoryList;
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_storeManager;
    protected $_order;
    protected $_bpsalescredit;
    protected $attributeRepository;
    protected $attributeValues;
    protected $tableFactory;
    protected $attributeOptionManagement;
    protected $optionLabelFactory;
    protected $optionFactory;
    protected $_pricelist;
    protected $_categorycollection;
    protected $_brandcollection;
    protected $_collectionattr;
    protected $_attributedata;
    protected $productFactory;
    protected $associatedproduct;
    protected $bptaxmapFactory;
    protected $_logManager;
    protected $_productInterfaceFactory;
    protected $_productRepository;
    protected $_productaction;
    protected $_pricelistmapfactory;
    protected $resource;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Sales\Model\Order $order,
        \Bsitc\Brightpearl\Model\PricelistFactory $pricelist,
        \Bsitc\Brightpearl\Model\BpsalescreditFactory $bpsalescredit,
        \Bsitc\Brightpearl\Model\CategoryFactory $categorycollection,
        \Bsitc\Brightpearl\Model\BrandFactory $brandcollection,
        \Bsitc\Brightpearl\Model\CustomattributeFactory $collectionattr,
        \Bsitc\Brightpearl\Model\AttributeFactory $attributedata,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Bsitc\Brightpearl\Model\AssociateproductFactory $associatedproduct,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Bsitc\Brightpearl\Model\BptaxmapFactory $bptaxmapFactory,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Action $productaction,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Bsitc\Brightpearl\Model\BppricelistmapFactory $pricelistmapfactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_directoryList                 = $directoryList;
        $this->_scopeConfig                 = $context->getScopeConfig();
        $this->_objectManager                  = $objectManager;
        $this->_storeManager                  = $storeManager;
        $this->attributeRepository             = $attributeRepository;
        $this->tableFactory                     = $tableFactory;
        $this->attributeOptionManagement    = $attributeOptionManagement;
        $this->optionLabelFactory             = $optionLabelFactory;
        $this->optionFactory                 = $optionFactory;
        $this->_pricelist                     = $pricelist;
        $this->_categorycollection             = $categorycollection;
        $this->_order                          = $order;
        $this->_bpsalescredit                  = $bpsalescredit;
        $this->_brandcollection              = $brandcollection;
        $this->_collectionattr                 = $collectionattr;
        $this->_attributedata                 = $attributedata;
        $this->productFactory                 = $productFactory;
        $this->associatedproduct             = $associatedproduct;
        $this->_bptaxmapFactory             = $bptaxmapFactory;
        $this->_logManager                     = $logManager;
        $this->_productInterfaceFactory     = $productInterfaceFactory;
        $this->_productRepository           = $productRepository;
        $this->_productaction               = $productaction;
        $this->_storeRepository             = $storeRepository;
        $this->_pricelistmapfactory         = $pricelistmapfactory;
        $this->_resource                     = $resource;
        $this->_connection                     = $this->_resource->getConnection();
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
    
    /*used in Observers*/
    public function getMagestoreBarcodeEnable()
    {
        return $bpenable  =  $this->getConfig('barcodesuccess/general/one_barcode_per_sku');
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


    /*Ecomm Attribute code*/
    public function getMgtProImgEnable()
    {
        $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/enable');
        if ($bpenable) {
            return $proimgscode  =  $this->getConfig('bpconfiguration/bpproduct/product_img');
        }
    }

    /*Get Colour attribute code*/
    public function getColour()
    {
        $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/enable');
        if ($bpenable) {
            return $colour  =  strtolower($this->getConfig('bpconfiguration/bpproduct/mgt_colour'));
        }
    }

    /*Get BP Colour*/
    public function getBpColour()
    {
        $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/enable');
        if ($bpenable) {
            return $colour  =  $this->getConfig('bpconfiguration/bpproduct/bp_colour');
        }
    }

    /*Get Size attribute code*/
    public function getSize()
    {
        $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/mgt_size');
        if ($bpenable) {
            return $size  =  strtolower($this->getConfig('bpconfiguration/bpproduct/mgt_size'));
        }
    }

    /*Get BP Size*/
    public function getBpSize()
    {
        $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/enable');
        if ($bpenable) {
            return $size  =  $this->getConfig('bpconfiguration/bpproduct/bp_size');
        }
    }

    /*Get Custom Values*/
    public function getCustomAttributes()
    {
        $custom_attribute = '';
        $bpenable  =  $this->getConfig('bpconfiguration/bpproduct/enable');
        if ($bpenable) {
            $custom_attribute =  $this->getConfig('bpconfiguration/bpproduct/custom_attribute');
        }
        return $custom_attribute;
    }


    /*Get Cancelled Order Status code*/
    public function getCancelledStatus()
    {
        $bpenable  =  $this->getConfig('bpconfiguration/bp_orderconfig/enable');
        if ($bpenable) {
            return $bpenable  =  $this->getConfig('bpconfiguration/bp_orderconfig/mgt_order_cancel');
        }
    }


    /*Ecomm Attribute code*/
    public function getPosConfig()
    {
        $pos  =  $this->getConfig('bpconfiguration/bpproduct/enable');
        if ($pos) {
            return $pos  =  $this->getConfig('bpconfiguration/bpproduct/pos_enable');
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


    public function isOrderExistInBpSalescredit($incrementId)
    {
        $collection = $this->_bpsalescredit->create()->getCollection();
        $collection->addFieldToFilter('mgt_order_id', ['eq'=>$incrementId]);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }

    public function isMagentoOrder($incrementId)
    {
        $order =   $this->_order->loadByAttribute('increment_id', $incrementId);
        if ($order->getId()) {
            return true;
        } else {
             return false;
        }
    }
    
    /*
    Get Attribute Is it exits or Not
    */
    
    public function getAttribute($attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
    }
    
    /*
    Create an attribute option and assign
    */
    public function createAttributeOptions($attributeCode, $label)
    {
        if (strlen($label) < 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Label for %1 must not be empty.', $attributeCode)
            );
        }

        // Does it already exist?
        
        $optionId = $this->getOptionId($attributeCode, $label);

        if (!$optionId) {
            $optionLabel = $this->optionLabelFactory->create();
            $optionLabel->setStoreId(0);
            $optionLabel->setLabel($label);
            $option = $this->optionFactory->create();
            $option->setLabel($label);
            $option->setStoreLabels([$optionLabel]);
            $option->setSortOrder(0);
            $option->setIsDefault(false);
            $this->attributeOptionManagement->add(
                \Magento\Catalog\Model\Product::ENTITY,
                $this->getAttribute($attributeCode)->getAttributeId(),
                $option
            );
            $optionId = $this->getOptionId($attributeCode, $label, true);
        }
        return $optionId;
    }
    
    /*
    Get Options id
    */
    
    public function getOptionId($attributeCode, $label, $force = false)
    {
        $attribute = $this->getAttribute($attributeCode);
        if ($force === true || !isset($this->attributeValues[ $attribute->getAttributeId() ])) {
            $this->attributeValues[ $attribute->getAttributeId() ] = [];
            $sourceModel = $this->tableFactory->create();
            $sourceModel->setAttribute($attribute);
            foreach ($sourceModel->getAllOptions() as $option) {
                $this->attributeValues[ $attribute->getAttributeId() ][ $option['label'] ] = $option['value'];
            }
        }
        if (isset($this->attributeValues[$attribute->getAttributeId()][$label])) {
            return $this->attributeValues[$attribute->getAttributeId()][$label];
        }
        return false;
    }
    
    /*
        Get Brand id for magento
    */
    public function getBrandId($bid)
    {
        $brandid = '';
        if ($bid) {
            $brandcollection = $this->_brandcollection->create();
            $brandcollection = $brandcollection->getCollection()->addFieldToFilter('bp_id', $bid);
            foreach ($brandcollection as $brandcoll) {
                    $brandid = $brandcoll->getMagentoId();
            }
        }
        return $brandid;
    }

    /*
        Get Collection id
    */
    public function getBpCollectionId($cid)
    {
        $data = '';
        if ($cid) {
            $collectionattr = $this->_collectionattr->create();
            $collectionattr = $collectionattr->getCollection()->addFieldToFilter('collection_id', $cid);
            foreach ($collectionattr as $collection) {
                    $data = $collection->getCustomData();
            }
        }
        return $data;
    }


    /*
        Get Attributes Options
    */
    public function getBpAttributeoptionId($code, $cid)
    {
                $cid = $cid;
                $code = $code;
                $data = '';
                $attributedata = $this->_attributedata->create();
                $attributedata = $attributedata->getCollection()
                                //->addFieldToFilter('attr_code', $code)
                                ->addFieldToFilter('mgt_code', $code)
                                ->addFieldToFilter('option_value_id', $cid);
        foreach ($attributedata as $collection) {
                $data = $collection->getMgOptionValueId();
        }


                return $data;
    }


    /*Create Price list and special price mapping for store level
        public function getPricelistMapping($bp_pricelist_id, $price_columnname){
            $enable  =  $this->getConfig('bpconfiguration/bpproduct/enable');
            $pricelistdata = [];
            if($enable){
                $pricelistcollections = $this->_pricelistmapfactory->create()->getCollection();
                $pricelistcollections = $pricelistcollections->addFieldToFilter($price_columnname, $bp_pricelist_id);
                $pricelistcollections = $pricelistcollections->getData();
                if(count($pricelistcollections)){
                    foreach($pricelistcollections as $pricelistcollection){
                        if($pricelistcollection['store_id'] >= 0){
                            $pricelistdata[] = $pricelistcollection['store_id'];
                        }
                    }
                }
            }
            return $pricelistdata;
        }*/



    public function setProductPrice($bp_product_id, $priceconfig)
    {
        $baseprice = 0;
        $collection  = $this->_pricelist->create()->getCollection()->addFieldToFilter('bp_product_id', $bp_product_id);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
            $pricelist_value = $data->getBpPricelist();
            $pricecolls = json_decode($pricelist_value, true);
            foreach ($pricecolls as $pricecoll) {
                if ($pricecoll['priceListId'] == $priceconfig) {
                    $d = $pricecoll['quantityPrice'];
                    foreach ($d as $prop) {
                        $baseprice = $prop;
                    }
                }
            }
        }
        return $baseprice;
    }


    public function loadBpPriceList($list_id)
    {
    }

    public function setPriceAndSpprice($bp_pro_id)
    {
        $PricelistconfigFactory = $this->_objectManager->create('Bsitc\Brightpearl\Model\PricelistconfigFactory');
        $pricealldatas = [];
        $pricelistcollections = $this->_pricelistmapfactory->create()->getCollection()->getData();
        if (count($pricelistcollections)) {
            foreach ($pricelistcollections as $pricelistcollection) {
                $storeid = $pricelistcollection['store_id'];
                $price = $pricelistcollection['bp_price'];
                $sppriceid = $pricelistcollection['bp_sp_price'];
                $pricealldatas[$storeid]['storeid'] = $storeid;
                $pricealldatas[$storeid]['price'] = $this->setProductPrice($bp_pro_id, $price);
                $pricealldatas[$storeid]['spprice'] = $this->setProductPrice($bp_pro_id, $sppriceid);
                $pricealldatas[$storeid]['price_lid'] = $price;
                $pricealldatas[$storeid]['spprice_lid'] = $sppriceid;
                $find = $PricelistconfigFactory->findRecord('bp_id', $price);
                if ($find) {
                    $pricealldatas[$storeid]['price_gross'] = $find->getGross();
                }
                $find = $PricelistconfigFactory->findRecord('bp_id', $sppriceid);
                if ($find) {
                    $pricealldatas[$storeid]['spprice_gross'] = $find->getGross();
                }
            }
        } else {
            $enable = $this->getConfig('bpconfiguration/bpproduct/enable');
            $storeid = 0;
            $price = 1;
            $sppriceid = 1;
            if ($enable) {
                $price = $this->getConfig('bpconfiguration/bpproduct/bp_pricelist');
                $sppriceid = $this->getConfig('bpconfiguration/bpproduct/bp_sppricelist');
            }
            $pricealldatas[$storeid]['storeid']         = $storeid;
            $pricealldatas[$storeid]['price']           = $this->setProductPrice($bp_pro_id, $price);
            $pricealldatas[$storeid]['spprice']         = $this->setProductPrice($bp_pro_id, $sppriceid);
            $pricealldatas[$storeid]['price_lid']       = $price;
            $pricealldatas[$storeid]['spprice_lid']     = $sppriceid;
            $find = $PricelistconfigFactory->findRecord('bp_id', $price);
            if ($find) {
                $pricealldatas[$storeid]['price_gross'] = $find->getGross();
            }
            $find = $PricelistconfigFactory->findRecord('bp_id', $sppriceid);
            if ($find) {
                $pricealldatas[$storeid]['spprice_gross'] = $find->getGross();
            }
        }
        return $pricealldatas;
    }



    /*Get configuration for Shipping Methods*/
    public function getTaxClass($taxclassid)
    {
        $taxablecode = '0';
        if ($taxclassid !="") {
             $taxcollections = $this->_bptaxmapFactory->create()->getCollection();
            $taxcollections = $taxcollections->addFieldToFilter('bpcode', $taxclassid);
            if ($taxcollections->getSize()) {
                $taxcollection  = $taxcollections->getFirstItem();
                $taxablecode     = $taxcollection['code'];
            } else {
                $taxablecode  =  $this->getConfig('bpconfiguration/bp_orderconfig/taxcode');
            }
        }
        return $taxablecode;
    }
    
    
    public function updateMgtProductPrice($bp_pro_id, $mgt_pro_id, $collection)
    {
        $arrayid = [];
        $arrayid[] = $mgt_pro_id;
        /*Fetch price by id and get Price of products*/
        $pricealldatas = $this->setPriceAndSpprice($bp_pro_id);
        if (!empty($pricealldatas)) {
            $configPriceCalculation = $this->getConfig('tax/calculation/price_includes_tax');
            $BptaxFactory = $this->_objectManager->create('Bsitc\Brightpearl\Model\BptaxFactory');
            $bpTaxRate = '';
            $rateFind = $BptaxFactory->findRecord('code', $collection->getTaxcodeCode());
            if ($rateFind) {
                $bpTaxRate = $rateFind->getRate() ;
            }
            foreach ($pricealldatas as $pricedata) {
                $baseprice = $pricedata['price'];
                $spprice = $pricedata['spprice'];
                $storeid = $pricedata['storeid'];
                $price_gross = $pricedata['price_gross'];
                $spprice_gross = $pricedata['spprice_gross'];
                if ((int)$configPriceCalculation == 1) {
                    if ($price_gross > 0 and $bpTaxRate > 0) {
                        $baseprice =  $baseprice + ( ( $baseprice * $bpTaxRate ) / 100 );
                    }
                    if ($spprice_gross > 0 and $bpTaxRate > 0) {
                        $spprice =  $spprice + ( ( $spprice * $bpTaxRate ) / 100 );
                    }
                }
                $this->_productaction->updateAttributes($arrayid, ['price' => $baseprice], $storeid);
                if ($spprice > 0) {
                    if ($baseprice > $spprice) {
                        $this->_productaction->updateAttributes($arrayid, ['special_price' => $spprice], $storeid);
                    }
                }
            }
        }
    }
       
    /*Update Products All Attributes*/
    public function UpdateProductsAllAttribute($collection, $type = 'simple')
    {
        $id = $collection->getMagentoId();
        $bp_pro_id = $collection->getProductId();
        $sku = $collection->getSku();
        $product = $this->productFactory->create();
        if ($id == '') {
            $id = $product->getIdBySku($sku);
        }
        /*Set Colour and Size*/
        if ($type == "configurable") {
             $sku = substr($sku, 0, 8);
            $id = $product->getIdBySku($sku);
        }
        $update_price_enable  =  $this->getConfig('bpconfiguration/bpproduct/update_price_enable');
        if ($update_price_enable) {
            $this->updateMgtProductPrice($bp_pro_id, $id, $collection) ;
        }
        $update_product_enable  =  $this->getConfig('bpconfiguration/bpproduct/update_product_enable');
        if (!$update_product_enable) {
            return true;
        }
        $arrayid = [];
        $arrayid[] = $id;
        if ($collection->getType() == 'simple') {
             $this->_productaction->updateAttributes($arrayid, ['bp_ean' => $collection->getEan()], 0);
             /*
             $this->_productaction->updateAttributes($arrayid, ['bp_ean' => $collection->getEan()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_upc' => $collection->getUpc()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_isbn' => $collection->getIsbn()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_mpn' => $collection->getMpc()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_barcode' => $collection->getBarcode()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_nominalcodestock' => $collection->getNominalPurchaseStock()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_nominalcodepurchases' => $collection->getNominalPurchasePurchase()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_nominalcodesales' => $collection->getNominalPurchaseSales()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_condition' => $collection->getCondition()], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_featured' => $collection->getFeatured()], 0);
             $brandid = $this->getBrandId($collection->getBrandId());
             $collectionid = $this->getBpCollectionId($collection->getCollectionId());
             $this->_productaction->updateAttributes($arrayid, ['bp_collection' => $collectionid], 0);
             $this->_productaction->updateAttributes($arrayid, ['bp_brand' => $brandid], 0);
             */
        }
        if ($collection->getType() == 'configurable' and $type == 'configurable') {
           /*Call function for Attribute options*/
            $season_datas = [];
            $seasonsid = [];
            if ($collection->getSeason()) {
                    $data = $collection->getSeason();
                    $season = ltrim($data, '[');
                    $season = rtrim($season, ']');
                    $season_datas = explode(",", $season);
                foreach ($season_datas as $season_data) {
                    $seasonid  = $season_data;
                    /*Call a function for season ids*/
                    $seasonsid[] =  $this->getBpAttributeoptionId('season', $seasonid);
                }
            } else {
                   $seasonsid[] = '';
            }
            if (count($seasonsid) > 0) {
                $firstSeasonsid = $seasonsid[0];
                $this->_productaction->updateAttributes($arrayid, ['season' => $firstSeasonsid], 0);
            }
        }
        return 'custom_product_updated';
    }

    public function getBpCategoryName($catid)
    {
        $name = '';
        $category = $this->_objectManager->create('Bsitc\Brightpearl\Model\Category');
        $collections = $category->getCollection()->addFieldToFilter('category_id', $catid);
        if ($collections->getSize()) {
            $item  = $collections->getFirstItem();
            $name = $item['name'];
        }
        return $name;
    }

    /*
    * Create a Products in magento 2
    */
    public function CreateProductsOld($collection, $type)
    {
        $producttypeid = 'simple';
        $collection = $collection;
        $bp_pro_id = $collection->getProductId();
        /*!!! Check if It is already exits Then update products by Webhooks!!!*/
        if ($collection->getMagentoId()) {
            $this->UpdateProductsAllAttribute($collection);
            return;
        }
        /*Check for Weight*/
        $dimensioncoll = $collection->getDimension();
        $dimension = json_decode($dimensioncoll);
        $weight = 0;
        $lenght = 0;
        $width  = 0;
        $height = 0;

        /*Weight for products*/
        if ($dimension) {
            if ($dimension->weight) {
                $dimensionwight = $dimension->weight;
                $weight = $dimensionwight->magnitude;
            }
            if ($dimension->dimensions) {
                $dimen = $dimension->dimensions;
                $lenght = $dimen->length;
                $height = $dimen->height;
                $width = $dimen->width;
            }
        }

        /*Assign Category with the Brightpearls products*/
        $cat_josn = $collection->getCategories();
        $cat = json_decode($cat_josn);
        $arrays =  (array) $cat;
        $catcollection_data = [];
        foreach ($arrays as $key => $value) {
            //$catcollection[] = $value->categoryCode;
            $bpcatid = $value->categoryCode;
            $catcollections = $this->_categorycollection->create();
            $catcollections = $catcollections->getCollection()->addFieldToFilter('category_id', $bpcatid);
            $finalcatid  = [];
            foreach ($catcollections as $catcollection) {
                if ($catcollection->getMgCategoryId()) {
                    $catcollection_data[] = $catcollection->getMgCategoryId();
                }
            }
        }


        if (count($catcollection_data) > 0) {
            $cat_data = $catcollection_data;
        } else {
            //$cat_data = array("2");
            $cat_data = '';
        }

        /*Set Brand in Magento products Call function*/
        if ($collection->getBrandId()) {
            $brandid = $this->getBrandId($collection->getBrandId());
        } else {
            $brandid = '';
        }

        /*Set Collection id in Magento products*/
        if ($collection->getCollectionId()) {
            $collectionid = $this->getBpCollectionId($collection->getCollectionId());
        } else {
            $collectionid = '';
        }

        /*Call function for Attribute options*/
        $season_datas = [];
        $seasonsid = [];

        if ($collection->getSeason()) {
            $data = $collection->getSeason();
            $season = ltrim($data, '[');
            $season = rtrim($season, ']');
            $season_datas = explode(",", $season);
            foreach ($season_datas as $season_data) {
                $seasonid  = $season_data;
                /*Call a function for season ids*/
                $seasonsid[] =  $this->getBpAttributeoptionId('bp_seasons', $seasonid);
            }
        } else {
                $seasonsid[] = '';
        }



        $colour = '';
        $size = '';
        $attribute_colour = '';
        $attribute_size = '';

        /*Start variations*/
        if ($this->getColour()) {
            $attribute_colour = $this->getColour();
        }

        if ($this->getSize()) {
            $attribute_size = $this->getSize();
        }


        $attribute_bpcolour = '';
        $attribute_bpsize = '';

        /*Start variations*/
        if ($this->getBpColour()) {
            $attribute_bpcolour = strtolower($this->getBpColour());
        }

        if ($this->getBpSize()) {
            $attribute_bpsize = strtolower($this->getBpSize());
        }


        if ($collection->getVariations()) {
            $variationcolls = json_decode($collection->getVariations());
            foreach ($variationcolls as $variationcoll) {
                    $name = strtolower($variationcoll->optionName);
                    /*Colour*/
                if ($name == $attribute_bpcolour) {
                    $id  = $variationcoll->optionValueId;
                    $colour = $this->getBpAttributeoptionId($attribute_colour, $id);
                }
                    /*Sizes*/
                if ($name == $attribute_bpsize) {
                    $id  = $variationcoll->optionValueId;
                    $size = $this->getBpAttributeoptionId($attribute_size, $id);
                }
            }
        }

        /*Start Fetch categories Mick*/
        if ($collection->getCategories()) {
            $categorycolls = json_decode($collection->getCategories());
            $catNames = [];
            if ($categorycolls) {
                foreach ($categorycolls as $categorycoll) {
                        $catid = $categorycoll->categoryCode;
                        $catNames[] = $this->getBpCategoryName($catid);
                }
            }
        }

        // $this->getAttributeType($code);

        if ($collection->getUpc()) {
            $upc = $collection->getUpc();
        } else {
            $upc = '';
        }

        if ($collection->getIsbn()) {
            $isbn = $collection->getIsbn();
        } else {
            $isbn = '';
        }

        if ($collection->getEan()) {
            $ean = $collection->getEan();
            $gtn = $collection->getEan();
        } else {
            $ean = '';
            $gtn = '';
        }

        if ($collection->getMpc()) {
            $mpc = $collection->getMpc();
        } else {
            $mpc = '';
        }
        $osbarcode = '';
        $barcode = '';
        if ($collection->getBarcode()) {
            if ($type == 'simple') {
                $barcode = $collection->getBarcode();
                $osbarcode = $collection->getBarcode();
            }
        } else {
            $barcode = '';
            $osbarcode = '';
        }


        if ($collection->getFeatured()) {
            $featured = $collection->getFeatured();
        } else {
            $featured = '';
        }

        if ($collection->getCondition()) {
            $condition = $collection->getCondition();
        } else {
            $condition = '';
        }


        if ($collection->getNominalPurchaseStock()) {
            $nominalcodestock = $collection->getNominalPurchaseStock();
        } else {
            $nominalcodestock = '';
        }


        if ($collection->getNominalPurchasePurchase()) {
            $PurchasePurchase = $collection->getNominalPurchasePurchase();
        } else {
            $PurchasePurchase = '';
        }

        if ($collection->getNominalPurchaseSales()) {
            $PurchaseSales = $collection->getNominalPurchaseSales();
        } else {
            $PurchaseSales = '';
        }

        if ($collection->getDescription()) {
            $description = $collection->getDescription();
        } else {
            $description = '';
        }


        if ($collection->getShortDescription()) {
            $short_description = $collection->getShortDescription();
        } else {
            $short_description = '';
        }

        $productgrpid = $collection->getProductGroupId();

        /*Create Umique product id*/
        $id   = $collection->getProductId();
        $name = $collection->getProductName();
        $randnumber = rand(10, 100);
        $url_keys = $name.$id.$randnumber;
        if ($type == "simple") {
            //$url_keys = $name.$id.$randnumber;
            $url_keys = $name.'-'.$id;
            $url_keys = str_replace(' ', '-', $url_keys);
        } else {
            $url_keys = 'bpconf'.'-'.$name.$id;
            $url_keys = str_replace(' ', '-', $url_keys);
        }

        /*Assign Tax For Products*/
        $taxcode = $collection->getTaxcodeCode();
        $producttax = 0;
        if ($taxcode) {
            $producttax    = $this->getTaxClass($taxcode);
        }
                
        /*Check for POS*/
        $pos = $this->getPosConfig();
        $sku = $collection->getSku();
        $pgroupid = $collection->getProductGroupId();
        $id   = $collection->getProductId();

        /*Set Colour and Size*/
        if ($type == "simple") {
            if ($sku) {
                $sku = $sku;
            } else {
                $sku = $collection->getProductName().'_'.$id;
            }
        } else {
            /*Configurable Product Dharmender*/
            if ($sku) {
                $sku = 'BPConfs_'.$id.'_'.$pgroupid;
            } else {
                $sku = 'BPConfs_'.$id.'_'.$pgroupid;
            }
        }
        /*Set data for products*/
        $product = $this->_productInterfaceFactory->create();

        try {
            $product->setSku($sku);

            $product->setName($collection->getProductName());

            $product->setProductType($producttypeid);

            $product->setAttributeSetId(4);

            $product->setStatus(0);


            $product->setDescription($description);

            $product->setShortDescription($short_description);

            $product->setUrlKey($url_keys);

            $product->setTaxClassId($producttax);

            $product->setWebposVisible($pos);

            $product->setBpBrand($brandid);
            
            $product->setBpUpc($upc);
            
            $product->setBpIsbn($isbn);

            $product->setBpMpn($mpc);
            
            $product->setBpBarcode($barcode);
            
            $product->setOsBarcode($osbarcode);

            $product->setBpNominalcodestock($nominalcodestock);

            $product->setBpNominalcodepurchases($PurchasePurchase);

            $product->setBpNominalcodesales($PurchaseSales);

            $product->setBpCollection($collectionid);

            $product->setBpCondition($condition);

            $product->setBpFeatured($featured);

            $product->setBpProductGroupId($productgrpid);

            $variations = $collection->getVariations();

            if ($variations) {
                if ($type == 'simple') {
                    $product->setVisibility(1);
                }
            } else {
                    $product->setVisibility(4);
            }

            if ($type == 'simple') {
                $product->setBpEan($ean);
                $product->setGtin($gtn);
            }

            //$this->_logManager->recordLog( json_encode($catNames, true) , "Cat Name", "Cat Name");
            if ($catNames) {
                $catname = implode(', ', $catNames);
                $product->setBpCategory($catname);
            }
    
            $product->setWeight($weight);

            //$product->setStoreId(0);
            /*Get all website ids*/

            $associate = $this->_resource->getTableName('store_website');
            $sql = $this->_connection->select()->from(['ce' => $associate], ['website_id']);
            $response = $this->_connection->query($sql);
            $results = $response->fetchAll(\PDO::FETCH_ASSOC);
            $allwebsitearray = [];
            foreach ($results as $result) {
                    $allwebsitearray[] = $result['website_id'];
            }
            $product->setWebsiteIds($allwebsitearray);

            //$product->setWebsiteIds(array(1,2,3,4,5,6));

            $pricealldatas = $this->setPriceAndSpprice($bp_pro_id);
            $this->_logManager->recordLog(json_encode($pricealldatas, true), "Product Price".$bp_pro_id, "Product Price");

            $createdproduct = $this->_productRepository->save($product);
            $mgt_pro_ids = '';

            if ($createdproduct) {
                $mgt_pro_ids = $createdproduct->getId();
                $arrayid[] = $mgt_pro_ids;
                $this->_productaction->updateAttributes($arrayid, ['weight' => $weight], 0);
                $this->_productaction->updateAttributes($arrayid, ['ts_dimensions_length' => $lenght], 0);
                $this->_productaction->updateAttributes($arrayid, ['ts_dimensions_width' => $width], 0);
                $this->_productaction->updateAttributes($arrayid, ['ts_dimensions_height' => $height], 0);
                $this->_productaction->updateAttributes($arrayid, ['bp_collection' => $collectionid], 0);

                    /*Start Update pric with store wise*/
                if (!empty($pricealldatas)) {
                    foreach ($pricealldatas as $pricedata) {
                                $baseprice = $pricedata['price'];
                                $spprice = $pricedata['spprice'];
                                $storeid = $pricedata['storeid'];
                                $this->_logManager->recordLog(json_encode($baseprice, true), "Product ".$bp_pro_id."Price ".$baseprice."Store ".$storeid, "Product Price");
                                $this->_productaction->updateAttributes($arrayid, ['price' => $baseprice], $storeid);
                        if ($spprice > 0) {
                            if ($baseprice > $spprice) {
                                $this->_productaction->updateAttributes($arrayid, ['special_price' => $spprice], $storeid);
                            }
                        }
                    }
                }
            
                /*Strat Set Custom fields*/
                $customfield_allvalues = [];
                if ($collection->getCustomField()) {
                    $customfields = json_decode($collection->getCustomField(), true);
                    if (array_key_exists('response', $customfields)) {
                        $customresponse    = $customfields['response'][$collection->getProductId()];
                        foreach ($customresponse as $key => $item) {
                            if (is_array($item)) {
                                if (array_key_exists('id', $item)) {
                                    $customfield_allvalues[$key] = $item['value'];
                                }
                            } else {
                                    $customfield_allvalues[$key] = $item;
                            }
                        }
                    }
                }
                /*Ends Custom Attributes*/
                try {
                        $configuregeCustomAttributes = $this->getCustomAttributes();
                         $configuregeCustomAttributesArray = [];
                    if ($configuregeCustomAttributes) {
                        $step1 = explode(",", $configuregeCustomAttributes);
                        foreach ($step1 as $step2) {
                            $step3 = explode(":", $step2);
                            if (array_key_exists($step3[0], $customfield_allvalues)) {
                                $attributeCode     = $step3[1];
                                $tmpKey         = $step3[0];
                                $attributeValue = $customfield_allvalues[$tmpKey];

                                $attributeInfo = $this->loadAttributeByCode($attributeCode);
                                if ($attributeInfo) {
                                    if ($attributeInfo->getFrontendInput() == 'select') {
                                        $attributeId = $attributeInfo->getAttributeId();
                                         $attributeValue = $this->getAttributeOptionId($attributeId, $attributeValue);
                                    }
                                        
                                    if ($attributeValue) {
                                        $this->_productaction->updateAttributes($arrayid, [$attributeCode => $attributeValue], 0);
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->_logManager->recordLog(json_encode($e->getMessage(), true), "Product", "Attributes");
                }
                 
            /*Ends Set Custom fields*/


                if ($type == 'simple') {
                    $this->_logManager->recordLog(json_encode($attribute_colour .'values '. $colour .' AND '. 'values '. $size . $attribute_size, true), "Product", "Attributes");
                    $this->_productaction->updateAttributes($arrayid, [$attribute_colour => $colour], 0);
                    $this->_productaction->updateAttributes($arrayid, [$attribute_size => $size], 0);
                }

                //$sku = $createdproduct->getSku();
                $this->_logManager->recordLog(json_encode($mgt_pro_ids, true), "Mgt Pro", "mgt pro ids");
            } else {
                $this->_logManager->recordLog(json_encode($collection->getId(), true), "Mgt Pro", "Products are not created!!!");
            }

            return     $mgt_pro_ids;
        /* Some logic that could throw an Exception */
        } catch (\Exception $e) {
            $this->_logManager->recordLog(json_encode($e->getMessage(), true), "Mgt Pro", "Error");
        }
    }

    public function CreateProducts($collection, $type)
    {
        if ($collection->getMagentoId()) {
            $res = $this->UpdateProductsAllAttribute($collection, $type);
            return $res;
        }
        $producttypeid = 'simple';
        $bp_pro_id = $collection->getProductId();
        $weight = 0;
        $lenght = 0;
        $height = 0;
        $width  = 0;
        $dimension = json_decode($collection->getDimension()); /* Check for Weight for products */
        if ($dimension) {
            if ($dimension->weight) {
                $weight = $dimension->weight->magnitude;
            }
            if ($dimension->dimensions) {
                $lenght = $dimension->dimensions->length;
                $height = $dimension->dimensions->height;
                $width = $dimension->dimensions->width;
            }
        }

        $colour = '';
        $size = '';
        $attribute_colour = '';
        $attribute_size = '';
        $attribute_bpcolour = '';
        $attribute_bpsize = '';
       /* --- Start variations ---- */
        if ($this->getColour()) {
            $attribute_colour = $this->getColour();
        }

        if ($this->getSize()) {
            $attribute_size = $this->getSize();
        }

        /*Start variations*/
        if ($this->getBpColour()) {
            $attribute_bpcolour = strtolower($this->getBpColour());
        }

        if ($this->getBpSize()) {
            $attribute_bpsize = strtolower($this->getBpSize());
        }

        if ($collection->getVariations()) {
            $variationcolls = json_decode($collection->getVariations());
            foreach ($variationcolls as $variationcoll) {
                $name = strtolower($variationcoll->optionName);
                /*Colour*/
                if ($name == $attribute_bpcolour) {
                    $id  = $variationcoll->optionValueId;
                    $colour = $this->getBpAttributeoptionId($attribute_colour, $id);
                }
                /*Sizes*/
                if ($name == $attribute_bpsize) {
                    $id  = $variationcoll->optionValueId;
                    $size = $this->getBpAttributeoptionId($attribute_size, $id);
                }
            }
        }

        /*Start Fetch categories Mick*/
        if ($collection->getCategories()) {
            $categorycolls = json_decode($collection->getCategories());
                    $catNames = [];
            if ($categorycolls) {
                foreach ($categorycolls as $categorycoll) {
                            $catid = $categorycoll->categoryCode;
                            $catNames[] = $this->getBpCategoryName($catid);
                }
            }
        }
            
        /*Call function for Attribute options*/
        $season_datas = [];
        $seasonsid = [];
        if ($collection->getSeason()) {
            $data = $collection->getSeason();
            $season = ltrim($data, '[');
            $season = rtrim($season, ']');
            $season_datas = explode(",", $season);
            foreach ($season_datas as $season_data) {
                     $seasonid  = $season_data;
                     /*Call a function for season ids*/
                     $seasonsid[] =  $this->getBpAttributeoptionId('season', $seasonid);
            }
        } else {
            $seasonsid[] = '';
        }
             
        $upc = $collection->getUpc();
        $isbn = $collection->getIsbn();
        $ean = $collection->getEan();
        $gtn = $collection->getEan();
        $mpc = $collection->getMpc();
        $featured = $collection->getFeatured();
        $condition = $collection->getCondition();
        $nominalcodestock = $collection->getNominalPurchaseStock();
        $PurchasePurchase = $collection->getNominalPurchasePurchase();
        $PurchaseSales = $collection->getNominalPurchaseSales();
        $description = trim($collection->getDescription());
        $short_description = trim($collection->getShortDescription());
        $productgrpid = $collection->getProductGroupId();
        $id = $collection->getProductId();
        $name = $collection->getProductName();
        $producttax = $this->getTaxClass($collection->getTaxcodeCode());
        $url_keys = '';
        $brandid = $this->getBrandId($collection->getBrandId());
        $collectionid = $this->getBpCollectionId($collection->getCollectionId());
        $barcode = '';
        $osbarcode = '';
        if ($collection->getBarcode() and $type == 'simple') {
            $barcode = $collection->getBarcode();
            $osbarcode = $collection->getBarcode();
        }
        /*Check for POS*/
           $pos = $this->getPosConfig();
           $sku = $collection->getSku();
           $pgroupid = $collection->getProductGroupId();
           $id = $collection->getProductId();
        /*Set Colour and Size*/
        if ($type == "simple") {
            if ($sku =="") {
                $sku = $collection->getProductId();
            }
        } elseif ($type == "configurable") {
            if ($sku =="") {
                $sku = 'HOH'.$collection->getProductId(); /* Configurable Product */
            } else {
                $sku = substr($sku, 0, 8);
            }
        } else {
            $sku = $collection->getProductId();
        }
        /*Set data for products*/
        $product = $this->_productInterfaceFactory->create();
        try {
            $product->setSku($sku);
            $product->setName($collection->getProductName());
            $product->setProductSubname($collection->getProductName());
            $product->setTitle($collection->getProductName());
            $product->setProductType($producttypeid);
            $product->setAttributeSetId(4);
            $product->setStatus(2);
            $product->setDescription($description);
            $product->setShortDescription($short_description);
            //$product->setUrlKey($url_keys);
            $product->setTaxClassId($producttax);
            // $product->setWebposVisible($pos);
            $product->setWeight($weight);
            $product->setVisibility(4);
            if ($collection->getVariations() and $type == 'simple') {
                $product->setVisibility(1);
            }
            if ($type == 'simple') {
                $product->setBpEan($gtn);
                $product->setBpUpc($upc);
                $product->setBpIsbn($isbn);
                $product->setBpMpn($mpc);
                $product->setBpBarcode($barcode);
                $product->setOsBarcode($osbarcode);
                $product->setBpNominalcodestock($nominalcodestock);
                $product->setBpNominalcodepurchases($PurchasePurchase);
                $product->setBpNominalcodesales($PurchaseSales);
                $product->setBpCollection($collectionid);
                $product->setBpCondition($condition);
                $product->setBpFeatured($featured);
                $product->setBpProductGroupId($productgrpid);
                //$product->setGtin($gtn);
            }
            if ($type == 'configurable') {
                $product->setVisibility(4);
                $product->setBpBrand($brandid);
                //$product->setBpUpc($upc);
                //$product->setBpIsbn($isbn);
                //$product->setBpMpn($mpc);
                //$product->setBpBarcode($barcode);
                //$product->setOsBarcode($osbarcode);
                //$product->setBpNominalcodestock($nominalcodestock);
                //$product->setBpNominalcodepurchases($PurchasePurchase);
                //$product->setBpNominalcodesales($PurchaseSales);
                //$product->setBpCollection($collectionid);
                //$product->setBpCondition($condition);
                //$product->setBpFeatured($featured);
                //$product->setBpProductGroupId($productgrpid);
            }
            if ($catNames) {
                $product->setBpCategory(implode(', ', $catNames));
            }
            //$product->setStoreId(0);
            /*Get all website ids*/
            $associate = $this->_resource->getTableName('store_website');
            $sql = $this->_connection->select()->from(['ce' => $associate], ['website_id']);
            $response = $this->_connection->query($sql);
            $results = $response->fetchAll(\PDO::FETCH_ASSOC);
            $allwebsitearray = [];
            foreach ($results as $result) {
                $allwebsitearray[] = $result['website_id'];
            }
            $product->setWebsiteIds($allwebsitearray);
            $pricealldatas = $this->setPriceAndSpprice($bp_pro_id);
            $this->_logManager->recordLog(json_encode($product->getData(), true), " Product Data", "Product Data");
            $createdproduct = $this->_productRepository->save($product);
            $mgt_pro_ids = '';
            if ($createdproduct) {
                $mgt_pro_ids = $createdproduct->getId();
                $arrayid[] = $mgt_pro_ids;
                $this->_productaction->updateAttributes($arrayid, ['tax_class_id' => $producttax], 0);
                /* Start Update pric with store wise */
                if (!empty($pricealldatas)) {
                    $configPriceCalculation = $this->getConfig('tax/calculation/price_includes_tax');
                    $BptaxFactory = $this->_objectManager->create('Bsitc\Brightpearl\Model\BptaxFactory');
                    $bpTaxRate = '';
                    $rateFind = $BptaxFactory->findRecord('code', $collection->getTaxcodeCode());
                    if ($rateFind) {
                        $bpTaxRate = $rateFind->getRate() ;
                    }
                    foreach ($pricealldatas as $pricedata) {
                        $baseprice = $pricedata['price'];
                        $spprice = $pricedata['spprice'];
                        $storeid = $pricedata['storeid'];
                        $price_gross = $pricedata['price_gross'];
                        $spprice_gross = $pricedata['spprice_gross'];
                        if ((int)$configPriceCalculation == 1) {
                            if ($price_gross > 0 and $bpTaxRate > 0) {
                                $baseprice =  $baseprice + ( ( $baseprice * $bpTaxRate ) / 100 );
                            }
                            if ($spprice_gross > 0 and $bpTaxRate > 0) {
                                $spprice =  $spprice + ( ( $spprice * $bpTaxRate ) / 100 );
                            }
                        }
                        $this->_productaction->updateAttributes($arrayid, ['price' => $baseprice], $storeid);
                        if ($spprice > 0) {
                            if ($baseprice > $spprice) {
                                $this->_productaction->updateAttributes($arrayid, ['special_price' => $spprice], $storeid);
                            }
                        }
                    }
                }
                if ($type == 'configurable') {
                    /*
                    $maincategory = 0;
                    $subcategory = 0;
                    if ($catNames) {
                        if (array_key_exists('1', $catNames)) {
                            $maincategory =  $this->createAttributeOptions('maincategory', $catNames[1]);
                        }
                        if (array_key_exists('1', $catNames)) {
                            $subcategory =  $this->createAttributeOptions('subcategory', $catNames[2]);
                        }
                    }
                    if ($maincategory > 0) {
                        $this->_productaction->updateAttributes($arrayid, ['maincategory' => $maincategory], 0);
                    }
                    if ($subcategory > 0) {
                        $this->_productaction->updateAttributes($arrayid, ['subcategory' => $subcategory], 0);
                    }
                    */
                    if (count($seasonsid) > 0) {
                        // $seasonsid = implode(",",$seasonsid);
                        $firstSeasonsid = $seasonsid[0];
                        $this->_productaction->updateAttributes($arrayid, ['season' => $firstSeasonsid], 0);
                        $this->_logManager->recordLog($firstSeasonsid, "First Seasons id", "Product Data");
                    }
                    $this->_productaction->updateAttributes($arrayid, ['bp_collection' => $collectionid], 0);
                    /* Strat Set Custom fields */
                    $customfield_allvalues = [];
                    if ($collection->getCustomField()) {
                        $customfields = json_decode($collection->getCustomField(), true);
                        if (array_key_exists('response', $customfields)) {
                            $customresponse    = $customfields['response'][$collection->getProductId()];
                            foreach ($customresponse as $key => $item) {
                                if (is_array($item)) {
                                    if (array_key_exists('id', $item)) {
                                        $customfield_allvalues[$key] = $item['value'];
                                    }
                                } else {
                                    $customfield_allvalues[$key] = $item;
                                }
                            }
                        }
                    }
                    /* Ends Custom Attributes */
                    try {
                            $configuregeCustomAttributes         = $this->getCustomAttributes();
                            $configuregeCustomAttributesArray     = [];
                        if ($configuregeCustomAttributes) {
                            $step1 = explode(",", $configuregeCustomAttributes);
                            foreach ($step1 as $step2) {
                                $step3 = explode(":", $step2);
                                if (array_key_exists($step3[0], $customfield_allvalues)) {
                                    $attributeCode     = $step3[1];
                                    $tmpKey         = $step3[0];
                                    $attributeValue = $customfield_allvalues[$tmpKey];
                                    $attributeInfo = $this->loadAttributeByCode($attributeCode);
                                    if ($attributeInfo) {
                                        if ($attributeInfo->getFrontendInput() == 'select' || $attributeInfo->getFrontendInput() == 'multiselect') {
                                            $attributeId = $attributeInfo->getAttributeId();
                                            $attributeValue = $this->getAttributeOptionId($attributeId, $attributeValue);
                                        }
                                            
                                        if ($attributeValue) {
                                            $this->_productaction->updateAttributes($arrayid, [$attributeCode => $attributeValue], 0);
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $this->_logManager->recordLog(json_encode($e->getMessage(), true), "Product", "Attributes");
                    }
                    /*Ends Set Custom fields*/
                }
 
                if ($type == 'simple') {
                    $this->_logManager->recordLog(json_encode($attribute_colour .' values '. $colour .' AND '. ' values '. $size . $attribute_size, true), "Product", "Attributes");
                    $this->_productaction->updateAttributes($arrayid, [$attribute_colour => $colour], 0);
                    $this->_productaction->updateAttributes($arrayid, [$attribute_size => $size], 0);
                }

                //$sku = $createdproduct->getSku();
                $this->_logManager->recordLog(json_encode($mgt_pro_ids, true), "Mgt Pro", "mgt pro ids");
            } else {
                $this->_logManager->recordLog(json_encode($collection->getId(), true), "Mgt Pro", "Products are not created!!!");
            }

            return     $mgt_pro_ids;
        /* Some logic that could throw an Exception */
        } catch (\Exception $e) {
            $this->_logManager->recordLog(json_encode($e->getMessage(), true), "Mgt Pro", "Error");
        }
    }
    
    /*Get Product Attributes*/
    public function getAttributeType($code)
    {
        return $this->loadAttributeByCode($code);
    }

        /*Check is Associated*/
    public function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

        /*Get Custom Attribute values*/
    public function getCustomAttributeValues($cid, $code)
    {
            $data = [];
            $collectionattr = $this->_collectionattr->create();
            //$collectionattr = $collectionattr->getCollection()->addFieldToFilter('collection_id', $cid);
            $collectionattr = $collectionattr->getCollection()
            ->addFieldToFilter('brand_id', $cid)
            ->addFieldToFilter('code', $code);
    
        if (count($collectionattr) > 0) {
            foreach ($collectionattr as $collection) {
                    //$data[] = $collection->getMgtCode();
                    $data[$collection->getMgtCode()] = $collection->getOptionValueId();
            }
             return $data;
        }
    }



    public function UpdateSyncStatus($childproductids)
    {
             $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $associate = $resource->getTableName('bsitc_brightpearl_associate_products');

        foreach ($childproductids as $childproductid) {
                 $updateAttributeArray = ['sync' => '1'];
                $where = ['mg_child_id = ?' => $childproductid];
                $connection->update($associate, $updateAttributeArray, $where);
        }
    }
        

        /*Assigning Simple products to configurable products*/
    public function getSimpleProductsId($id)
    {
        try {
            /*Fetch super and child products ids*/
             $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $associate = $resource->getTableName('bsitc_brightpearl_associate_products');
             $sql = $connection->select()->from(['ce' => $associate], ['mg_sup_id','mg_child_id'])->where('sync = ?', '0')->where('mg_sup_id = ?', $id)->distinct(true);
             $results = $connection->fetchAll($sql);
            $simpleproductids = [];
            foreach ($results as $result) {
                    $simpleproductids[] = $result['mg_child_id'];
            }
            return $simpleproductids;
        } catch (\Exception $e) {
             $this->_logManager->recordLog($e->getMessage(), "getSimpleProductsId", "getSimpleProductsId Products");
        }
    }


        /*Set Visibility for products*/
    public function setProductsVisibility($associatedProductIds)
    {
        foreach ($associatedProductIds as $associatedProductId) {
               $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($associatedProductId); // Load simple and update visibility Product
              $product->setVisibility(1);
              $product->setStoreId(0);
              $product->save();
        }
    }


    /*Array for Insert Queries*/
    public function InsertQueryArrayData($productId, $attributeId, $postion)
    {
        return $data[] = [
                            'product_super_attribute_id' => null,
                            'attribute_id'                  => $attributeId,
                            'product_id'                    => $productId,
                            'position'                      => $postion
            ];
    }


    public function setAssociateProducts()
    {
        try {
             $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $associate = $resource->getTableName('bsitc_brightpearl_associate_products');

            $bpproduct_table = $resource->getTableName('bsitc_brightpearl_products');
             $sql = $connection->select()->from(['ce' => $associate], ['mg_sup_id'])
                 ->group('mg_sup_id')
                ->where('sync = ?', 0);
             $collections = $connection->fetchAll($sql);


            foreach ($collections as $collection) {
                  //$associatedProductIds = array(105,107,108);
                  $attrbute_value = [];
                    
                  $productId = $collection['mg_sup_id'];
                  $associatedProductIds = $this->getSimpleProductsId($productId);

                  /*Check products have colour and size*/
                  $sql = $connection->select()->from(['ce' => $bpproduct_table], ['variations'])
                      ->where('conf_pro_id = ?', $productId);
                   $results = $connection->fetchAll($sql);
                     
                   $this->_logManager->recordLog(json_encode($results), "configurable", "Associated Products");

                foreach ($results as $result) {
                    foreach ($result as $rslt) {
                              $data = json_decode($rslt);
                        if (count($data) >= 2) {
                            if ($data[0]) {
                                       $attrbute_value[] = strtolower($data[0]->optionName);
                            }
                            if ($data[1]) {
                                      $attrbute_value[] = strtolower($data[1]->optionName);
                            }
                        } elseif (count($data) == 1) {
                            if ($data[0]) {
                                 $attrbute_value[] = strtolower($data[0]->optionName);
                            }
                        }
                    }
                       break;
                }

                   $this->_logManager->recordLog(json_encode($attrbute_value), "configurable", "Associated Products");


                if (!empty($associatedProductIds)) {
                    /*Not Indivisually show*/
                    $this->setProductsVisibility($associatedProductIds);

                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId); // Load Configurable Product
                    $attributeModel = $this->_objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute');
                    $position = 0;
                    
                    //$attributes = array(185, 186); // Super Attribute Ids Used To Create Configurable Product
                    $attributes = [];
                    //$attributeColour = 'colour';

                    $attributeColour = '';
                    if ($this->getColour()) {
                         $attributeColour = $this->getColour();
                    }

                      $attributebpColour = strtolower($this->getBpColour());

                      $attributebpSize = strtolower($this->getBpSize());

                      $entityType    = 'catalog_product';
                    if (in_array($attributebpColour, $attrbute_value)) {
                      //if($attributeColour){
                        $attributeInfo = $this->_objectManager->get(\Magento\Eav\Model\Entity\Attribute::class);
                        $attributeInfo = $attributeInfo->loadByCode($entityType, $attributeColour);
                        if ($attributeInfo->getAttributeId()) {
                            $attributes[]   = $attributeInfo->getAttributeId();
                        }
                    }

                    //$attributeSize = 'size';
                      $attributeSize = '';
                    if ($this->getSize()) {
                        $attributeSize = $this->getSize();
                    }

                    if (in_array($attributebpSize, $attrbute_value)) {
                      //if($attributeSize){
                          $attributeInfo = $this->_objectManager->get(\Magento\Eav\Model\Entity\Attribute::class);
                          $attributeInfo = $attributeInfo->loadByCode($entityType, $attributeSize);
                            
                        if ($attributeInfo->getAttributeId()) {
                            $attributes[]    = $attributeInfo->getAttributeId();
                        }
                    }

                    /*Configurable products ids*/
                      $this->_logManager->recordLog(json_encode($attributes), "conf pro id - ". $productId, "Attributes");
                      $sql = $connection->select()->from(['ce' => 'catalog_product_entity'])->where('entity_id = ?', $productId);
                      $response = $connection->query($sql);
                      $result = $response->fetch(\PDO::FETCH_ASSOC);
                    if ($result) {
                        /*
                        $rowpro_id = @$result['row_id'];
                        if ($rowpro_id > 0 ) {
                            $rowpro_id  = $rowpro_id;
                        } else {
                            $rowpro_id = $productId;
                        }
                        */
                        $rowpro_id = $productId;

                        foreach ($attributes as $attributeId) {
                            $data = ['attribute_id' => $attributeId, 'product_id' => $rowpro_id, 'position' => $position];
                            $position++;
                            $attributeModel->setData($data)->save();
                        }
                    

                        $product->setTypeId("configurable"); // Setting Product Type As Configurable
                        $product->setAffectConfigurableProductAttributes(4);
                        $this->_objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->setUsedProductAttributeIds($attributes, $product);
                        $product->setNewVariationsAttributeSetId(4); // Setting Attribute Set Id
                        $product->setAssociatedProductIds($associatedProductIds);// Setting Associated Products
                        $product->setCanSaveConfigurableAttributes(true);
                        $product->setStoreId(0);
                        $saveproduct = $product->save();
                        if ($saveproduct) {
                            $this->UpdateSyncStatus($associatedProductIds);
                        }
                    }
                }
                  /*Unset Attribute array values for colour and size attributes*/
                  unset($attributes);
            }
        } catch (\Exception $e) {
             $this->_logManager->recordLog($e->getMessage(), "setAssociateProducts", "Associate Products");
        }
    }



    /*Check Products Id already exits*/
    public function checkProductExits($id)
    {
            $productid      = $id;
             $bpproducts = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpproducts');
            $collections = $bpproducts->getCollection()->addFieldToFilter('product_id', $productid);
            $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }


    public function CheckProductType($groupid)
    {

        $groupid      = $groupid;
         $bpproducts = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpproducts');
        $collections = $bpproducts->getCollection()->addFieldToFilter('product_group_id', $groupid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }

    /*Update products custom tables*/
    public function UpdateProductWebhooks($result, $id)
    {
            $results = $result;
            $responses = $results['response'];
            $productdata = [];
            $id = $id;
             $bpproducts = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpproducts');
            $collections = $bpproducts->getCollection()->addFieldToFilter('product_id', $id);

        foreach ($collections as $collection) {
                /*increment id*/
                $pid = $collection->getId();
                /*load data by id*/
                $setdata = $bpproducts->load($pid);
                /*Set value in tables*/
            if (is_array($responses)) {
                foreach ($responses as $response) {
                    $productdata['brand_id'] = $response['brandId'];
                    if (array_key_exists("collectionId", $response)) {
                                        $productdata['collection_id'] = $response['collectionId'];
                    } else {
                                                $productdata['collection_id'] = '';
                    }

                    if (array_key_exists("productTypeId", $response)) {
                                        $productdata['product_type_id'] = $response['productTypeId'];
                    } else {
                                                                $productdata['product_type_id'] = '';
                    }
                                            

                    if (array_key_exists("featured", $response)) {
                        $productdata['featured'] = $response['featured'];
                    } else {
                        $productdata['featured'] = '';
                    }


                                                        $productdata['sku'] = $response['identity']['sku'];

                    if (array_key_exists("isbn", $response['identity'])) {
                        $productdata['isbn'] = $response['identity']['isbn'];
                    } else {
                        $productdata['isbn'] = '';
                    }


                    if (array_key_exists("upc", $response['identity'])) {
                        $productdata['upc'] = $response['identity']['upc'];
                    } else {
                        $productdata['upc'] = '';
                    }

                    if (array_key_exists("ean", $response['identity'])) {
                        $productdata['ean'] = $response['identity']['ean'];
                    } else {
                        $productdata['ean'] = '';
                    }
                                            
                    if (array_key_exists("mpn", $response['identity'])) {
                        $productdata['mpc'] = $response['identity']['mpn'];
                    } else {
                        $productdata['mpc'] = '';
                    }

                    if (array_key_exists("barcode", $response['identity'])) {
                        $productdata['barcode'] = $response['identity']['barcode'];
                    } else {
                        $productdata['barcode'] = '';
                    }
                                            
                    if (array_key_exists("productGroupId", $response)) {
                        $productdata['product_group_id'] = $response['productGroupId'];
                    } else {
                        $productdata['product_group_id'] = '';
                    }
                                            

                                                        $productdata['dimension'] = json_encode($response['stock']);

                                                        $productdata['taxcode_id'] = $response['financialDetails']['taxCode']['id'];
                                                        $productdata['taxcode_code'] = $response['financialDetails']['taxCode']['code'];

                                                        $productdata['sales_channel_name'] = $response['salesChannels']['0']['salesChannelName'];
                                                        $productdata['product_name'] = $response['salesChannels']['0']['productName'];

                                                        $productdata['categories'] = json_encode($response['salesChannels']['0']['categories']);
                                            
                                                        $productdata['description'] = $response['salesChannels']['0']['description']['text'];
                                            
                                                        $productdata['short_description'] = $response['salesChannels']['0']['shortDescription']['text'];


                                                        $productdata['condition'] = $response['salesChannels']['0']['productCondition'];

                    if (array_key_exists("createdOn", $response)) {
                        $productdata['created_at'] = $response['createdOn'];
                    } else {
                        $productdata['created_at'] = date('Y-m-d H:i:s');
                    }

                    if (array_key_exists("updatedOn", $response)) {
                        $productdata['updated_at'] = $response['updatedOn'];
                    } else {
                        $productdata['updated_at'] = '';
                    }

                    if (array_key_exists("seasonIds", $response)) {
                        $productdata['season'] = json_encode($response['seasonIds']);
                    } else {
                        $productdata['season'] = '';
                    }

                                                        $productdata['state'] = 'updated';
                                                        $productdata['warehouse'] =  json_encode($response['warehouses']);
                                                        ;
                                                        $productdata['nominal_purchase_stock'] = $response['nominalCodeStock'];
                                                        $productdata['nominal_purchase_purchase'] = $response['nominalCodePurchases'];
                                                        $productdata['nominal_purchase_sales'] = $response['nominalCodeSales'];
                                                        $productdata['status'] = $response['status'];


                                                        $setdata->setData($productdata);
                                                        $setdata->setId($pid);
                                                        $setdata->save();
                }
            }
        }
    }




    /*Insert Products in custom tables bulk import*/
    public function setProductsCustomtable($range)
    {

                 $bpproducts = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpproducts');
                $data = $range;
                $responses = $data['response'];

                $productdata = [];

        foreach ($responses as $response) {
                $productdata['product_id'] = $response['id'];
                $productdata['brand_id'] = $response['brandId'];

            if (array_key_exists("collectionId", $response)) {
                    $productdata['collection_id'] = $response['collectionId'];
            } else {
                        $productdata['collection_id'] = '';
            }

            if (array_key_exists("productTypeId", $response)) {
                $productdata['product_type_id'] = $response['productTypeId'];
            } else {
                $productdata['product_type_id'] = '';
            }
                        
            if (array_key_exists("featured", $response)) {
                $productdata['featured'] = $response['featured'];
            } else {
                $productdata['featured'] = '';
            }

                            $productdata['sku'] = $response['identity']['sku'];

            if (array_key_exists("isbn", $response['identity'])) {
                $productdata['isbn'] = $response['identity']['isbn'];
            } else {
                $productdata['isbn'] = '';
            }


            if (array_key_exists("upc", $response['identity'])) {
                $productdata['upc'] = $response['identity']['upc'];
            } else {
                $productdata['upc'] = '';
            }

            if (array_key_exists("ean", $response['identity'])) {
                $productdata['ean'] = $response['identity']['ean'];
            } else {
                $productdata['ean'] = '';
            }
                        
            if (array_key_exists("mpn", $response['identity'])) {
                $productdata['mpc'] = $response['identity']['mpn'];
            } else {
                $productdata['mpc'] = '';
            }

            if (array_key_exists("barcode", $response['identity'])) {
                $productdata['barcode'] = $response['identity']['barcode'];
            } else {
                $productdata['barcode'] = '';
            }

            if (array_key_exists("productGroupId", $response)) {
                $productdata['product_group_id'] = $response['productGroupId'];
            } else {
                $productdata['product_group_id'] = '';
            }

                        //$productdata['dimension'] = $response['dimensions'];
                            $productdata['dimension'] = json_encode($response['stock']);

                            $productdata['taxcode_id'] = $response['financialDetails']['taxCode']['id'];
                            $productdata['taxcode_code'] = $response['financialDetails']['taxCode']['code'];

                            $productdata['sales_channel_name'] = $response['salesChannels']['0']['salesChannelName'];
                            $productdata['product_name'] = $response['salesChannels']['0']['productName'];

                            $productdata['categories'] = json_encode($response['salesChannels']['0']['categories']);
                        
                            $productdata['description'] = $response['salesChannels']['0']['description']['text'];
                        
                            $productdata['short_description'] = $response['salesChannels']['0']['shortDescription']['text'];


                            $productdata['condition'] = $response['salesChannels']['0']['productCondition'];


                            $productdata['created_at'] = $response['createdOn'];

                        
            if (array_key_exists("updatedOn", $response)) {
                $productdata['updated_at'] = $response['updatedOn'];
            } else {
                $productdata['updated_at'] = '';
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
                }
            } else {
                $productdata['variations'] = '';
                $productdata['type'] = 'simple';
            }



            if (array_key_exists("seasonIds", $response)) {
                $productdata['season'] = json_encode($response['seasonIds']);
            } else {
                $productdata['season'] = '';
            }

                        
                            $productdata['state'] = 'new';
                            $productdata['warehouse'] =  json_encode($response['warehouses']);
            ;
                            $productdata['nominal_purchase_stock'] = $response['nominalCodeStock'];
                            $productdata['nominal_purchase_purchase'] = $response['nominalCodePurchases'];
                            $productdata['nominal_purchase_sales'] = $response['nominalCodeSales'];
                            $productdata['status'] = $response['status'];
                            $productdata['syc_status'] = 0;
            if ($productdata) {
                $bpproducts->setData($productdata);
                $bpproducts->save();
            }
        }
    }

    /*Set Price for Custom tables*/
    public function setPriceCustomtable($responses)
    {
         $bpprice = $this->_objectManager->create('Bsitc\Brightpearl\Model\Pricelist');
        $responses = $responses;
        $responses = $responses['response'];
        foreach ($responses as $response) {
                            $pricedata['bp_product_id'] = $response['productId'];
                            $pricedata['bp_pricelist'] = json_encode($response['priceLists']);
                            $pricedata['mg_product_id'] = '';
                            $pricedata['sync'] = 0;
                            $bpprice->setData($pricedata);
                            $bpprice->save();
        }
    }
    

    public function loadAttributeByCode($attributeCode)
    {

        $attributeInfo = '';
        if ($attributeCode) {
            $entityType = 'catalog_product';
            $entityAttribute = $this->_objectManager->create('\Magento\Eav\Model\Entity\Attribute');
            $attributeInfo =  $entityAttribute->loadByCode($entityType, $attributeCode);
        }
        return $attributeInfo;
    }


    public function getAttributeOptionId($attributeId, $attributeValue)
    {
        $optionId = '';
        $attributeOptionAll = $this->_objectManager->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class)
            ->setPositionOrder('asc')
            ->setAttributeFilter($attributeId)
            ->setStoreFilter()
            ->load();
        if (count($attributeOptionAll) > 0) {
            foreach ($attributeOptionAll as $attributeOption) {
                if (trim($attributeOption->getValue()) == trim($attributeValue)) {
                    $optionId = $attributeOption->getId();
                    break;
                }
            }
        }
        return $optionId;
    }
}
