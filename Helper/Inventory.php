<?php

namespace Bsitc\Brightpearl\Helper;

class Inventory extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_directoryList;
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_storeManager;
    protected $attributeValues;
    protected $productFactory;
    protected $moduleManager;
    protected $_warehouseFactory;
    protected $_webhookinventory;
    protected $_api;
    protected $_bpproductsFactory;
    protected $_logManager;
    protected $_product;
    protected $_connection;
    protected $_resource;
    public $_date;
    
    private $_sourceItemsSave;
    private $_sourceItemInterface;
    
    protected $_stockRegistry;
    protected $_productaction;
    
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\BpproductsFactory $bpproductsFactory,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\WarehouseFactory $warehouseFactory,
        \Bsitc\Brightpearl\Model\WebhookinventoryFactory $webhookinventory,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSave,
        \Magento\InventoryApi\Api\Data\SourceItemInterface $sourceItemInterface,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
		\Magento\Catalog\Model\Product\Action $productaction
    ) {
        $this->_date                 = $date;
        $this->_directoryList         = $directoryList;
        $this->_scopeConfig         = $context->getScopeConfig();
        $this->_objectManager          = $objectManager;
        $this->_storeManager          = $storeManager;
        $this->productFactory         = $productFactory;
        $this->moduleManager         = $moduleManager;
        $this->_warehouseFactory    = $warehouseFactory;
        $this->_webhookinventory    = $webhookinventory;
        $this->_bpproductsFactory   = $bpproductsFactory;
        $this->_logManager             = $logManager;
        $this->_api                   = $api;
        $this->_product             = $product;
        $this->_resource             = $resource;
        $this->_connection             = $this->_resource->getConnection();
        
        $this->_sourceItemsSave         = $sourceItemsSave;
        $this->_sourceItemInterface     = $sourceItemInterface;
		
		$this->_stockRegistry = $stockRegistry;
		$this->_productaction = $productaction;
 
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
    
    public function getBrightpearl($store)
    {
        $apiObj = '';
          $bpConfigData  =  (array) $this->getConfig('bpconfiguration/api', $store);
        if (isset($bpConfigData['enable']) && isset($bpConfigData['bp_useremail']) && isset($bpConfigData['bp_password']) && isset($bpConfigData['bp_account_id']) && isset($bpConfigData['bp_dc_code']) && isset($bpConfigData['bp_api_version'])) {
            $apiObj    = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Api', ['data' => $bpConfigData]);
        }
        return $apiObj;
    }
    
    
    public function getInventoryEnable()
    {
        return $InvtConfigData  =  $this->getConfig('bpconfiguration/bpinventory/enable');
    }
    
    public function updateInventoryStuckQueue()
    {
        $adminHours = 0;
        if (!$adminHours) {
            $adminHours = 1;
        }
        $collection = $this->_webhookinventory->create()->getCollection()->addFieldToFilter('status', 'processing');
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

    public function InventorySync()
    {
        // --------------- Check if Multimodule are enables ---------------
        if ($this->moduleManager->isOutputEnabled('Magestore_InventorySuccess')) {
        } else {
            $stocktransfer = $this->_objectManager->create('\Bsitc\Brightpearl\Model\StocktransferFactory');
             $this->updateInventoryStuckQueue();  // ------- update stuck queue records
             // ---------------  check is any process running ---------------
            if ($this->_webhookinventory->checkQueueProcessingStatus()) {
                return true;
            }

                     
            // ---------------  Inventory Collections with pending status ---------------
            $collections = $this->_webhookinventory->create()->getCollection()->addFieldToFilter('status', 'pending');
            if ($collections->getSize()) {
                // ----------- set processing state to stop the parallel processing -------------
                $tmpCollections = $collections;
                foreach ($tmpCollections as $item) {
                    $item->setStatus('processing')->save();
                }
     
                /*---------- new -----------*/
                    $warehouseMapping = [];
                    $warehouseMappingCollection = $this->_warehouseFactory->create()->getCollection();
                foreach ($warehouseMappingCollection as $item) {
                    $warehouseMapping[$item->getMgtLocation()][] = $item->getBpWarehouse();
                }
                     $allwarehouseObj    = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
                    $allwarehouseArray    = $allwarehouseObj->toArray();
                     
                 /*---------- new -----------*/
                foreach ($collections as $item) {
                    $updateDataArray = [ "status" => "error"];
                    $this->_webhookinventory->updateRecord($item->getId(), $updateDataArray);
                    
                    $bp_pid = $item->getBpId();
                    $mgt_pid = $this->IsProductExits($bp_pid); // ------------ get magento product id ---------
                    $sku = $this->getProductSku($bp_pid); // ------------ get magento product sku ---------

                    /*check if product does not exits*/
                    if (empty($mgt_pid)) {
                        $response = $this->_api->getProductById($bp_pid);
                        if (array_key_exists('response', $response)) {
                            /*Check if product sku*/
                            $sku = $response['response'][0]['identity']['sku'];
                            $mgt_pid = $this->checkProductExitsInMagento(trim($sku));
                        }
                    }
                    if ($mgt_pid) {
                        $updateDataArray = [ "mgt_id" => $mgt_pid, "sku" => $sku ];
                        $this->_webhookinventory->updateRecord($item->getId(), $updateDataArray);

                        $responseStock = $this->_api->fetchProductStock($bp_pid);
 
                        if (array_key_exists("response", $responseStock)) {
                             $inStock   = $responseStock['response'][$bp_pid]['total']['inStock'];
                            $onHandQty = $responseStock['response'][$bp_pid]['total']['onHand'];
                            $allocated = $responseStock['response'][$bp_pid]['total']['allocated'];
                            
                             $qty = 0;
                            $allstoretotalqty = 0;
                            $warehousename = '';

                            $multiplestores = [];
                            $newonhandQty = 0;
                            
                            /*---------- new -----------*/
                            if ($onHandQty !== '') {
                                $warehouseids =  $responseStock['response'][$bp_pid]['warehouses'];
                                $whQtyArray = [];
                                foreach ($warehouseids as $wid => $warehouse) {
                                    $onHandQty         = $warehouse['onHand'];
                                    $intransitQty     = $stocktransfer->getProductQtyBySku($bp_pid, $wid);
                                    if ($intransitQty > 0) {
                                        $onHandQty = $onHandQty + $intransitQty;
                                    }
                                    $whQtyArray[$wid] = $onHandQty;
                                }
                                                                    
                                foreach ($warehouseMapping as $locId => $locations) {
                                    $finalQty = 0;
                                    $finalWidArray = [];
                                    $finalWnameArray = [];
                                    foreach ($locations as $location) { // locatin meand warehouse id of brightpearl
                                        $finalWidArray[]     = $location;
                                        $finalWnameArray[]     = $allwarehouseArray[$location];
                                        if (array_key_exists($location, $whQtyArray)) {
                                            $finalQty  += $whQtyArray[$location];
                                        } else {
                                            $intransitQty     = $stocktransfer->getProductQtyBySku($bp_pid, $location);
                                            if ($intransitQty > 0) {
                                                $finalQty  += $intransitQty;
                                            }
                                        }
                                    }
                                    $finalWid            = implode(",", $finalWidArray);
                                    $finalWname            = implode(",", $finalWnameArray);
                                    $finalQty             = intval($finalQty);
                                    $allstoretotalqty     += $finalQty;
                                        
                                    $stockid         = $this->getStockId($locId);
                                    
                                    $updateDataArray = ["updated_inventory" => $finalQty,"warehouse_id" => $finalWid,"warehouse_name" => $finalWname];
                                    $this->_webhookinventory->updateRecord($item->getId(), $updateDataArray);
                                        
                                    //Get Qty form magento tables
                                    // $previousqty = $this->getMagentoInventory($mgt_pid, $locId, $stockid);
                                    $previousqty = $this->getMsiInventory($sku, $locId);
                                         
                                    if ($previousqty) {
                                        $pqty = floatval($previousqty);
                                        $updateDataArray = ["old_inventory" => $pqty];
                                        $this->_webhookinventory->updateRecord($item->getId(), $updateDataArray);
                                    }
                                    /*End comments new here*/

                                    $logMsg = 'Store id : '. $locId .' | Pre Qty : '.(int)$previousqty.'| New Qty : '.(int)$finalQty;
                                    $this->_logManager->recordLog($logMsg, $sku, "Inventory Update");
                                        
                                    /*Update Inventory*/
                                    // $result = $this->InventoryUpdate($mgt_pid, $finalQty, $locId, $stockid);
                                    $result = $this->UpdateMsiInventory($sku, $finalQty, $locId);
									
									if($finalQty > 0 ){
										$this->enableStockManagement($mgt_pid);
									}
                                        
                                     $updateDataArray = [ "status" => "complete"];
                                    $this->_webhookinventory->updateRecord($item->getId(), $updateDataArray);
                                }
                            }
                                /*---------- new -----------*/
 
                            //*Update Qty subtotals from all warehouses*//
                            // $this->UpdateDefaultTotalStock($allstoretotalqty, $mgt_pid);
                        } else {
                            $this->_logManager->recordLog('Unable to get response from Brightpearl', "Inventory Update", "Inventory  Cron");
                        }
                    } else {
                        $updateDataArray = [ "mgt_id" => "Product Does not exits."];
                        $this->_webhookinventory->updateRecord($item->getId(), $updateDataArray);
                        $this->_logManager->recordLog('Product Does not exits in Magento', "Inventory Update", "Inventory  Cron");
                    }
                }
            }
        }
    }
    
    public function getMagentoInventory($entity_id, $store_id, $stock_id)
    {
        $associate = $this->_resource->getTableName('cataloginventory_stock_item');
        $qty = 0;
        if (($entity_id) && ($store_id) && ($stock_id)) {
            $sql = $this->_connection->select()->from(['ce' => $associate], ['qty'])
                ->where('product_id = ?', $entity_id)
                ->where('website_id = ?', $store_id)
                ->where('stock_id = ?', $stock_id);
            $response = $this->_connection->query($sql);
            $result = $response->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $qty = $result['qty'];
            }
        }
        return $qty;
    }


    public function checkProductExitsInMagento($sku)
    {
        $mgt_id = '';
        $associate = $this->_resource->getTableName('catalog_product_entity');
        $sql = $this->_connection->select()->from(['ce' => $associate], ['entity_id'])
            ->where('sku = ?', $sku);
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $mgt_id = $result['entity_id'];
        }
        return $mgt_id;
    }


    public function IsProductExits($bpid)
    {
        $mgtproductid = '';
        $data = $this->_bpproductsFactory->findRecord('product_id', $bpid);
        if ($data) {
            $mgtproductid = $data->getMagentoId();
        }
        return $mgtproductid;
    }


    /*Get Products sku*/
    public function getProductSku($bpid)
    {
        $sku = '';
        $data = $this->_bpproductsFactory->findRecord('product_id', $bpid);
        if ($data) {
            $sku = $data->getSku();
        }
        return $sku;
    }


    public function CheckGroupInventory($warehouseid)
    {
            $collections = $this->_warehouseFactory->create()->getCollection()->addFieldToFilter('bp_warehouse', [['finset'=> [$warehouseid]]]);
            $warehouses  = $collections->getData();
            
            $storeid = [];
            
        foreach ($warehouses as $warehouse) {
            $storeid[]     = ['location' => $warehouse['mgt_location'], 'warehouse' => $warehouse['bp_warehouse']];
        }

            return $storeid;
    }

    public function getMgtStoreId($warehouseid)
    {
            $warehouseid = $warehouseid;
            $collections = $this->_warehouseFactory->create()->getCollection()->addFieldToFilter('bp_warehouse', [['finset'=> [$warehouseid]]]);
            $warehouses  = $collections->getData();
            $warehouseids = [];
            $storeid = [];
        foreach ($warehouses as $warehouse) {
            $storeid['mgtlocation']     = $warehouse['mgt_location'];
            $storeid['warehouseid']     = $warehouse['bp_warehouse'];
        }
            return $storeid;
    }
    
    public function getStockId($websiteid)
    {
         $stockid = 0;
         $associate = $this->_resource->getTableName('cataloginventory_stock');
        $sql = $this->_connection->select()->from(['ce' => $associate], ['stock_id'])->where('website_id = ?', $websiteid);
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $stockid = $result['stock_id'];
        }
        return $stockid;
    }
    
    /*Get Warehouse Name (Not Used Right Now)*/
    public function getWarehouseName($stockid)
    {
        $stockname = '';
        $associate = $this->_resource->getTableName('cataloginventory_stock');
        $sql = $this->_connection->select()->from($associate)->where('stock_id = ?', $stockid) ;
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $stockname = $result['stock_name'];
        }
        return $stockname;
    }
    
    
    public function InventoryUpdate($entity_id, $totalqty, $websiteid, $stockid)
    {
           $associate = $this->_resource->getTableName('cataloginventory_stock_item');
         $catalog_product_entity = $this->_resource->getTableName('catalog_product_entity');
        if (($entity_id) && ($websiteid) && ($stockid)) {
            $sql = $this->_connection->select()->from($associate)
                          ->where('product_id = ?', $entity_id)
                          ->where('website_id = ?', $websiteid)
                          ->where('stock_id = ?', $stockid);
             $response = $this->_connection->query($sql);
            $results = $response->fetch(\PDO::FETCH_ASSOC);
            if ($results) {
                if ($totalqty > 0) {
                    
                     $updateAttributeArray = ['qty' => $totalqty, 'low_stock_date' => '', 'is_in_stock' => '1', 'manage_stock' => '1', 'use_config_manage_stock' => '1'];
                    $where = ['product_id = ?' => $entity_id, 'website_id = ?' => $websiteid, 'stock_id = ?' => $stockid ];
                    $this->_connection->update($associate, $updateAttributeArray, $where);
                    
                    $update_date = date("Y-m-d H:i:s") ;
                    $whereCondition = ['entity_id = ?' => $entity_id];
                    $this->_connection->update($catalog_product_entity, ['updated_at' => $update_date], $whereCondition);
                } else {
                    $totalqty = 0 ;
                    $updateAttributeArray = ['qty' => $totalqty, 'low_stock_date' => '', 'is_in_stock' => '0'];
                    $where = ['product_id = ?' => $entity_id, 'website_id = ?' => $websiteid, 'stock_id = ?' => $stockid ];
                    $this->_connection->update($associate, $updateAttributeArray, $where);
                    
                    $update_date = date("Y-m-d H:i:s") ;
                    $whereCondition = ['entity_id = ?' => $entity_id];
                    $this->_connection->update($catalog_product_entity, ['updated_at' => $update_date], $whereCondition);
                }
            } else {
                $is_in_stock = ($totalqty > 0 ? 1 : 0);
                if ($stockid == 1) {
                    $websiteid = 0;
                }
                $selfqty = '';
                $data = $this->InsertQueryArrayData($entity_id, $stockid, $totalqty, $selfqty, $is_in_stock, $websiteid);
                $this->_connection->insertMultiple($associate, $data);
                $update_date = date("Y-m-d H:i:s") ;
                $whereCondition = ['entity_id = ?' => $entity_id];
                $this->_connection->update($catalog_product_entity, ['updated_at' => $update_date], $whereCondition);

                //$this->UpdateQtyWebhookQueue($entity_id, $totalqty, $field="updated_inventory");
                if ($totalqty > 0) {
                    $this->checkInventorySuperPro($entity_id, $stockid, $websiteid);
                }
            }
        }
    }

    public function checkInventorySuperPro($entity_id_child, $stockid, $websiteid)
    {

        $inventoryStockItemTable = $this->_resource->getTableName('cataloginventory_stock_item');
        $associateProductsTable = $this->_resource->getTableName('bsitc_brightpearl_associate_products');
        $sql = $this->_connection->select()->from(['ce' => $associateProductsTable], ['mg_sup_id'])->where('mg_child_id = ?', $entity_id_child);
        $results = $this->_connection->fetchAll($sql);
        if (count($results) > 0) {
            foreach ($results as $result) {
                $entity_id_sup = $result['mg_sup_id'];
                
                $sql_a = $this->_connection->select()->from($inventoryStockItemTable)
                    ->where('product_id = ?', $entity_id_sup)
                    ->where('website_id = ?', $websiteid)
                    ->where('stock_id = ?', $stockid);
                $response = $this->_connection->query($sql_a);
                $result_a = $response->fetch(\PDO::FETCH_ASSOC);
                if (!$result_a) {
                    $totalqty = 0;
                    $selfqty = '';
                    $is_in_stock = 1;
                    $data = $this->InsertQueryArrayData($entity_id_sup, $stockid, $totalqty, $selfqty, $is_in_stock, $websiteid);
                    $this->_connection->insertMultiple($inventoryStockItemTable, $data);
                }

                $stockid_b = '1' ;
                $sql_b = $this->_connection->select()->from($inventoryStockItemTable)
                    ->where('product_id = ?', $entity_id_sup)
                    ->where('stock_id = ?', $stockid_b);
                    
                $response_b = $this->_connection->query($sql_b);
                $result_b = $response_b->fetch(\PDO::FETCH_ASSOC);
                if ($result_b) {
                    $updateAttributeArray = ['website_id' => '0', 'is_in_stock' => '1', ];
                    $where = ['product_id = ?' => $entity_id_sup, 'stock_id = ?' => '1' ];
                    $this->_connection->update($inventoryStockItemTable, $updateAttributeArray, $where);
                }
            }
        }
    }

    public function UpdateDefaultTotalStock($totalqty, $entity_id)
    {
         $inventoryStockItemTable =  $this->_resource->getTableName('cataloginventory_stock_item');
        $updateAttributeArray = ['qty' => $totalqty,  'low_stock_date' => '', 'is_in_stock' => '1', 'website_id' => '0' ];
        $where = ['product_id = ?' => $entity_id, 'stock_id = ?' => '1' ];
        $this->_connection->update($inventoryStockItemTable, $updateAttributeArray, $where);
    }

    
    /*Array for Insert Queries*/
    public function InsertQueryArrayData($entity_id, $stockid, $totalqty, $selfqty, $is_in_stock, $websiteid)
    {
        return $data[] = [
                            'product_id'                    =>         $entity_id,
                            'stock_id'                        =>        $stockid,
                            'qty'                            =>         $totalqty,
                            'shelf_location'                =>         $selfqty,
                            'min_qty'                        =>         0.0000,
                            'use_config_min_qty'             =>         1,
                            'is_qty_decimal'                =>         0,
                            'backorders'                    =>         0,
                            'use_config_backorders'            =>         1,
                            'min_sale_qty'                    =>         1.0000,
                            'use_config_min_sale_qty'        =>         1,
                            'max_sale_qty'                    =>         0.0000,
                            'use_config_max_sale_qty'         =>         1,
                            'is_in_stock'                    =>         $is_in_stock,
                            'low_stock_date'                =>         null,
                            'notify_stock_qty'                =>         null,
                            'use_config_notify_stock_qty'    =>         1,
                            'manage_stock'                    =>         0,
                            'use_config_manage_stock'         =>        1,
                            'stock_status_changed_auto'        =>         0,
                            'use_config_qty_increments'        =>         1,
                            'qty_increments'                =>         0.0000,
                            'use_config_enable_qty_inc'        =>         1,
                            'enable_qty_increments'            =>         0,
                            'is_decimal_divided'            =>         0,
                            'website_id'                    =>         $websiteid,
                            'updated_time'                    =>         'CURRENT_TIMESTAMP'
            ];
    }
    
    
    
    /* --------------- multi soucre inventroy update ------------- */
    public function UpdateMsiInventory($sku, $qty, $source_code)
    {
        $is_in_stock = ($qty > 0 ? 1 : 0);
        $this->_sourceItemInterface->setSku($sku);
        $this->_sourceItemInterface->setQuantity($qty);
        $this->_sourceItemInterface->setStatus(1);
        $this->_sourceItemInterface->setSourceCode($source_code);
        $this->_sourceItemsSave->execute([$this->_sourceItemInterface]);
    }
    public function getMsiInventory($sku, $source_code)
    {
        $qty = 0;
        $associate = $this->_resource->getTableName('inventory_source_item');
        if (($sku) && ($source_code)) {
            $sql = $this->_connection->select()->from(['ce' => $associate], ['quantity'])
                ->where('sku = ?', $sku)
                ->where('source_code = ?', $source_code);
             $response = $this->_connection->query($sql);
            $result = $response->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $qty = $result['quantity'];
            }
        }
        return $qty;
    }
	
    public function enableStockManagement($productId)
    {
        usleep(1000000); /* 1 sec = 1000000 microseconds  */
        //$product    =    $this->_product->load($productId); //load product which you want to update stock
        $stockItem    =    $this->_stockRegistry->getStockItem($productId); // load stock of that product
         $stockItem->setData('manage_stock', 1);  // stock_data  manage_stock
        $stockItem->setData('use_config_manage_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
        $stockItem->setData('is_in_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
        $stockItem->save(); //save stock of item
        //$product->save(); //  also save product
        $this->_productaction->updateAttributes([$productId], ['is_madetoorder' => 0, 'is_printtoorder' => 0 ], 0);
    }
	
	
	
}
