<?php

namespace Bsitc\Brightpearl\Model;

class BpproductimageFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_api;

    protected $_bpproduct;

    protected $_productinterface;

    protected $_storeManager;

    protected $_datahelper;

    protected $_logManager;

    protected $_collectionFactory;

    protected $_con;

    protected $_date;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Bsitc\Brightpearl\Model\Api $api,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\BpproductsFactory $bpproduct,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productinterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bsitc\Brightpearl\Helper\Data $datahelper,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\App\ResourceConnection $con,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_api                  = $api;
        $this->_objectManager        = $objectManager;
        $this->_bpproduct            = $bpproduct;
        $this->_productinterface     = $productinterface;
        $this->_storeManager         = $storeManager;
        $this->_datahelper           = $datahelper;
        $this->_logManager           = $logManager;
        $this->_collectionFactory    = $collectionFactory;
        $this->_con                  = $con;
        $this->_date                 = $date;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpproductimage', $arguments, false);
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

    /*Insert Data in log tables*/
    public function recordLog($cat, $log_data, $title)
    {
         $logArray = [];
        if ($cat) {
            $cat = $cat;
        } else {
            $cat = "Global";
        }
         $logArray['category'] = $cat;
         $logArray['title'] =  $title;
         $logArray['store_id'] =  0;
         $logArray['error'] =  json_encode($log_data, true);
         $this->_logManager->addLog($logArray);
        return true;
    }

    /*Sync All products*/
    public function syncAllProducts()
    {
        if ($this->_api->authorisationToken) {
                /*get Collection for all products*/
                $productobj = $this->_bpproduct->create()->getCollection();
                $collections = $productobj->getData();
                
            foreach ($collections as $collection) {
                    $productdata = [];
                    $imagepath   = '';
                    $mgtproid    = $collection['magento_id'];

                if ($mgtproid) {
                    $imagepath = $this->getProductImages($mgtproid);
                }

                if ($imagepath) {
                        $productdata['bp_id']          =  $collection['product_id'];
                        $productdata['mgt_id']         =  $mgtproid;
                        $productdata['sku']            =  $collection['sku'];
                        $productdata['img_url']        =  $imagepath;
                        $productdata['status']         =  1;
                    try {
                        if ($productdata['sku']!="") {
                            /*-------------Check Records Already exits --------------*/
                            $finddata = $this->findRecord('mgt_id', $mgtproid);
                            if ($finddata) {
                                $id = $finddata['id'];
                                $row = ['img_url' =>  $imagepath, 'status' => 1];
                                /*-------------If Exits then update records --------------*/
                                $this->updateRecord($id, $row);
                            } else {
                                /*-------------Save records ------------------------------*/
                                $this->addRecord($productdata);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->_logManager->recordLog($e->getMessage(), "SyncAllproImg", "SyncAllproImg");
                    }
                }
            }
        }
    }

    /*Brightpearl Magento Images Syncs*/
    public function SyncImagetoBp()
    {
        if ($this->_api->authorisationToken) {
            /*Get Code from Config */
            $mgt_img_code  = $this->_datahelper->getMgtProImgEnable();
            $collections = $this->create()->getCollection()->addFieldToFilter('status', 1)->getData();

            try {
                foreach ($collections as $collection) {
                        $id = $collection['id'];
                        $bpid = $collection['bp_id'];
                    if ($collection['img_url']) {
                        $data = '[
                                        {
                                        "op":    "add",
                                        "path":  "/'.$mgt_img_code.'",
                                        "value": "'.$collection['img_url'].'"
                                        }
                            ]';
                        $response  = $this->_api->postProductImagetoBp($bpid, $data);
                        $log_data = "Product Images ".json_encode($response);
                        $this->recordLog($cat = "Product Img", $log_data, $title = $bpid);
                        /*-------------Update status sync as 3 for complete status --------------*/
                        $row = ['status' =>  3];
                        $this->updateRecord($id, $row);
                    }
                }
            } catch (\Exception $e) {
                $this->_logManager->recordLog($e->getMessage(), "SyncImgtoBp", "SyncImgtoBp");
            }
        }
    }

    /*Sync Magento Products which are update in last 24 hrs*/
    public function SyncMgtProUpdate()
    {
        if ($this->_api->authorisationToken) {
               $objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
               //$objDate = $this->_date->create();
               $date = $objDate->gmtDate('Y-m-d');

                $connection = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
                //$connection = $this->_con->create();
                $conn = $connection->getConnection();
                $select = $conn->select()
                    ->from(
                        ['main_table' => 'catalog_product_entity'],
                        [
                            'main_table.entity_id'
                        ]
                    )
                    ->join(
                        ['custom_table' => 'bsitc_brightpearl_products'],
                        'main_table.entity_id = custom_table.magento_id'
                    )->where("main_table.updated_at >= $date");
                $collections  = $conn->fetchAll($select);

            foreach ($collections as $collection) {
                    $mgtproid = $collection['entity_id'];

                if ($mgtproid) {
                    $imagepath = $this->getProductImages($mgtproid);
                }
                if ($imagepath) {
                        $productdata['bp_id']          =  $collection['product_id'];
                        $productdata['mgt_id']         =  $mgtproid;
                        $productdata['sku']            =  $collection['sku'];
                        $productdata['img_url']        =  $imagepath;
                        $productdata['status']         =  1;
                    try {
                        if ($productdata['sku']!="") {
                            /*-------------Check Records Already exits --------------*/
                            $finddata = $this->findRecord('mgt_id', $mgtproid);
                            if ($finddata) {
                                $id = $finddata['id'];
                                if ($finddata['img_url'] != $imagepath) {
                                        //$row = array('img_url' =>  $imagepath);
                                        $row = ['img_url' =>  $imagepath, 'status' => 1];
                                    /*-------------If Exits then update records --------------*/
                                        $this->updateRecord($id, $row);
                                }
                            } else {
                                /*-------------Save records ------------------------------*/
                                $this->addRecord($productdata);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->_logManager->recordLog($e->getMessage(), "SyncImgtoBp", "Mgtproid:".$mgtproid);
                    }
                }
            }
        }
    }

    /*Brightpearl Magento Images Syncs*/
    public function getProductImages($pid)
    {
        if ($this->_api->authorisationToken) {
            $image = '';
            try {
                /*Get base Image for products*/
                $product = $this->_productinterface->create()->getById($pid);
                if ($product) {
                        /*Get Images full paths*/
                        $baseimgpath  = $product->getData('image');
                    if ($baseimgpath) {
                        if ($baseimgpath != "no_selection") {
                            $productimages = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
                            $image = $productimages . $baseimgpath;
                        }
                    }
                }
                return $image;
            } catch (\Exception $e) {
                $this->_logManager->recordLog($e->getMessage(), "SyncImgtoBp", "SyncImgtoBp");
            }
        }
    }
}
