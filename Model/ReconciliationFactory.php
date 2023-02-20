<?php

namespace Bsitc\Brightpearl\Model;

class ReconciliationFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    public $_logManager;
    public $_inventoryWarehouseMapping;
    public $_api;
    public $_scopeConfig;
    protected $_resource;
    protected $_connection;
    
    public $_queuestatus;
    public $goReleaseState;
    public $goProcessingState;
    public $itReceivedState;
    public $goErrorState;
    

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Bsitc\Brightpearl\Model\WarehouseFactory $inventoryWarehouseMapping,
        \Bsitc\Brightpearl\Model\Api $api,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_objectManager                 = $objectManager;
        $this->_logManager                    = $logManager;
        $this->_inventoryWarehouseMapping    = $inventoryWarehouseMapping;
        $this->_api                            = $api;
        $this->_scopeConfig                    = $scopeConfig;
        $this->_resource                    = $resource;
        $this->_connection                     = $this->_resource->getConnection();
        
        $this->_queuestatus                 = $queuestatus;
        
        $queue_status                        = $this->_queuestatus->getQueueOptionArray();
        $this->goReleaseState                = $queue_status['GO_Release'];
        $this->goProcessingState            = $queue_status['GO_Processing'];
        $this->itReceivedState                = $queue_status['IT_Received'];
        $this->goErrorState                    = $queue_status['GO_Error'];
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Reconciliation', $arguments, false);
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
 
    public function checkRecordExits($sku, $location_id)
    {
        $data = '';
        $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
        $sql = $this->_connection->select()->from(['bsr' => $bsr])
            ->where('sku = ?', $sku)
            ->where('location_id = ?', $location_id);
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $data = $result;
        }
        return $data;
    }
    
    
    public function findMgtRecord($bpid, $wid)
    {
        $data = '';
        $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
        $sql = $this->_connection->select()->from(['bsr' => $bsr])
            ->where('bp_id = ?', $bpid)
            ->where('warehouse_id = ?', $wid);
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            //$data = $result['id'];
            $data = $result;
        }
        return $data;
    }
    
    public function findMgtRecordNew($bpid, $location_id)
    {
        $data = '';
        $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
        $sql = $this->_connection->select()->from(['bsr' => $bsr])
            ->where('bp_id = ?', $bpid)
            ->where('location_id = ?', $location_id);
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $data = $result;
        }
        return $data;
    }
    
    
    public function refreshReport()
    {
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $stock_id = [];
         $stock_id = $this->_inventoryWarehouseMapping->create()->getCollection()->addFieldToSelect('mgt_location')->getColumnValues('mgt_location');
         
        if (count($stock_id) > 0) {
            $isi = $this->_resource->getTableName('inventory_source_item');
            $cpe = $this->_resource->getTableName('catalog_product_entity');
            $bbw = $this->_resource->getTableName('bsitc_brightpearl_warehouse');
            $bbbi = $this->_resource->getTableName('bsitc_brightpearl_bpitems');
            
            $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
             $this->_connection->truncateTable($bsr);
            
            $selectColumns= [    "isi.source_item_id", "isi.source_code", "isi.sku", "isi.quantity", "isi.status", "cpe.entity_id", "cpe.sku", "cpe.type_id", "bbw.bp_warehouse", "bbw.mgt_location", "bbbi.bp_id", "bbbi.bp_ptype"];
                     
            $sql = $this->_connection->select()->from(["isi"=>$isi], [])
            ->joinLeft(["cpe"=>$cpe], "isi.sku = cpe.sku", [])
            ->joinLeft(["bbw"=>$bbw], "isi.source_code = bbw.mgt_location", [])
            ->join(["bbbi"=>$bbbi], "cpe.sku = bbbi.bp_sku", $selectColumns)
            ->where('isi.source_code IN (?)', $stock_id);
            
            $results = $this->_connection->fetchAll($sql);
               
            $finalResults = [];
            foreach ($results as $item) {
                $tmpKey = $item['stock_id'].'_'.$item['bp_id'];
                
                $row                     = [];
                $row['warehouse_id']     = $item['bp_warehouse'];
                $row['location_id']     = $item['stock_id'];
                $row['sku']             = $item['sku'];
                $row['mgt_qty']         = intval($item['quantity']);
                $row['bp_id']             = $item['bp_id'];
                $row['bp_ptype']         = $item['bp_ptype'];
                 $finalResults[$tmpKey]     = $row ;
            }
            
            $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
            $this->_connection->insertMultiple($bsr, $finalResults);
        }
        return true;
    }


    public function refreshFromBp()
    {
        
        
        $inventoryWarehouseMapping     = $this->_objectManager->create('Bsitc\Brightpearl\Model\WarehouseFactory');
        $stocktransfer                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $allwarehouseObj            = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
        $allwarehouseArray             = $allwarehouseObj->toArray();
        
        $alreadyInsertedData = $this->getExistingDataArray();

        $warehouseMapping = [];
        $warehouseMappingCollection = $inventoryWarehouseMapping->create()->getCollection();
        foreach ($warehouseMappingCollection as $item) {
            $warehouseMapping[$item->getMgtLocation()][] = $item->getBpWarehouse();
        }
         
        $getUris = [];
         $collection = $this->create()->getCollection()->getColumnValues('bp_id') ;
         $collectionRangeString =  $this->getRangeString($collection, '200');
        
        $msg  = 'Total Chunks for Api Call '.count($collectionRangeString);
        $this->_logManager->recordLog($msg, "Stock Reconciliation", "Stock Reconciliation");
        
        if (count($collectionRangeString) > 0) {
            $bpAllStockData = [];
            foreach ($collectionRangeString as $key => $range) {
                 $bpAllStockData[] = $this->_api->getProductsAvailability($range);
            }
            
            $msg  = 'Brightpearl All Stock ('.count($bpAllStockData).') Fetch Time '.date('Y-m-d H:i:s');
            $this->_logManager->recordLog($msg, "Stock Reconciliation", "Stock Reconciliation");
             
            //foreach($collectionRangeString as $key=>$range)
            foreach ($bpAllStockData as $key => $items) {
                  // $msg = $key.' - Range , Start at '.date('Y-m-d H:i:s');
                   //$items = $this->_api->getProductsAvailability($range);
                if (count($items) > 0) {
                    foreach ($items as $bpid => $value) {
                        $whQtyArray = [];
                        foreach ($value['warehouses'] as $wid => $warehouse) {
                            $onHandQty         = $warehouse['onHand'];
                            $intransitQty     = $stocktransfer->getProductQtyBySku($bpid, $wid);
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
                                }
                            }
                            $finalWid        = implode(",", $finalWidArray);
                            $finalWname        = implode(",", $finalWnameArray);
                            $finalQty         = intval($finalQty);
                            
                            // ---------------- new code here ------------------
                            $tmpKey = $locId.'_'.$bpid;
                            if (array_key_exists($tmpKey, $alreadyInsertedData)) {
                                $alreadyInsertedData[$tmpKey]['bp_qty']         = $finalQty;
                                $alreadyInsertedData[$tmpKey]['warehouse_id']     = $finalWid;
                                $alreadyInsertedData[$tmpKey]['warehouses']     = $finalWname;
                                $alreadyInsertedData[$tmpKey]['diff']            = $alreadyInsertedData[$tmpKey]['mgt_qty'] - $finalQty;
                            }
                        }
                    }
                }
                 //$msg .= ' End at '.date('Y-m-d H:i:s');
                 //$this->_logManager->recordLog($msg, "Range", "Stock Reconciliation" );
            }
            $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
            $this->_connection->truncateTable($bsr);
            $this->_connection->insertMultiple($bsr, $alreadyInsertedData);
              $msg = ' Final End at '.date('Y-m-d H:i:s');
            $this->_logManager->recordLog($msg, "Stock Reconciliation", "Stock Reconciliation");
        }
        return true;
    }

    public function getRangeString($numberArray, $chunks)
    {
        $returnResult = [];
         $numberArray = array_unique($numberArray);
        $numberArray = array_filter($numberArray) ;
        sort($numberArray);
        $collectionChunks = array_chunk($numberArray, 200);
        foreach ($collectionChunks as $chunk) {
             //$number_array = array_map('intval', explode(',', $csv)); // split string using the , character
             // Loop through array and build range string
            $range_string         = '';
            $previous_number     = intval(array_shift($chunk));
            $range                 = false;
            $range_string         = "" . $previous_number;
            foreach ($chunk as $number) {
                $number = intval($number);
                if ($number == $previous_number + 1) {
                    $range = true;
                } else {
                    if ($range) {
                        $range_string .= "-$previous_number";
                        $range = false;
                    }
                    $range_string .= ",$number";
                }
                $previous_number = $number;
            }
            if ($range) {
                $range_string .= "-$previous_number";
            }
             $returnResult[] = $range_string;
        }
        return $returnResult;
    }
    
    public function refreshBundleStock()
    {
        
        $inventoryWarehouseMapping     = $this->_objectManager->create('Bsitc\Brightpearl\Model\WarehouseFactory');
        $stocktransfer                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $allwarehouseObj            = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
        $allwarehouseArray             = $allwarehouseObj->toArray();

        $warehouseMapping = [];
        $warehouseMappingCollection = $inventoryWarehouseMapping->create()->getCollection();
        foreach ($warehouseMappingCollection as $item) {
            $warehouseMapping[$item->getMgtLocation()][] = $item->getBpWarehouse();
        }
        
         $collection = $this->create()->getCollection()->addFieldToFilter('bp_ptype', '1');
        if (count($collection) > 0) {
            foreach ($collection as $items) {
                $bp_id = $items['bp_id'];
                 $collection = $this->_api->getBundleAvailability($bp_id);
                if (array_key_exists("response", $collection)) {
                    $response = $collection['response'];
                    if (array_key_exists($bp_id, $response)) {
                        $result = $response[$bp_id];
                        $whQtyArray = [];
                        foreach ($result['warehouses'] as $wid => $warehouse) {
                            $onHandQty         = $warehouse['onHand'];
                            $intransitQty     = $stocktransfer->getProductQtyBySku($bp_id, $wid);
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
                                }
                            }
                            $finalWid        = implode(",", $finalWidArray);
                            $finalWname        = implode(",", $finalWnameArray);
                            $finalQty         = intval($finalQty);

                            $data             = $this->findMgtRecordNew($bp_id, $locId);
                            if ($data) {
                                $id                     = $data['id'];
                                $row                    = [];
                                $row['bp_qty']             = $finalQty;
                                $row['warehouse_id']    = $finalWid;
                                $row['warehouses']        = $finalWname;
                                $row['diff']             = $data['mgt_qty'] - $finalQty;
                                $this->updateRecord($id, $row);
                            }
                        }
                    }
                }
            }
        }
    }

    public function getExistingDataArray()
    {
        $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
        $sql = $this->_connection->select()->from(['bsr' => $bsr]);
         $result = $this->_connection->fetchAll($sql);
        $finalResults = [];
        foreach ($result as $item) {
              $tmpKey = $item['location_id'].'_'.$item['bp_id'];
             $finalResults[$tmpKey]     = $item ;
        }
          return $finalResults;
    }
    
    public function executeReport()
    {
        
        
        $inventoryWarehouseMapping     = $this->_objectManager->create('Bsitc\Brightpearl\Model\WarehouseFactory');
        $stocktransfer                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $allwarehouseObj            = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
        $allwarehouseArray             = $allwarehouseObj->toArray();
        $stocktTransferDataArray    = $this->getStocktTransferData();
        
        $warehouseMapping = [];
        $warehouseMappingCollection = $inventoryWarehouseMapping->create()->getCollection();
        foreach ($warehouseMappingCollection as $item) {
            $warehouseMapping[$item->getMgtLocation()][] = $item->getBpWarehouse();
        }
         
        $getUris = [];
         
         $collectionAb             = $this->getBpIdCollectionNew();
         
        $collection             = $collectionAb['A'];
        $alreadyInsertedData     = $collectionAb['B'];
         
         $collectionRangeString =  $this->getRangeString($collection, '200');
        
        $msg  = 'Total Chunks for Api Call '.count($collectionRangeString);
        $this->_logManager->recordLog($msg, "Stock Reconciliation", "Stock Reconciliation");
        
        if (count($collectionRangeString) > 0) {
            $bpAllStockData = [];
            foreach ($collectionRangeString as $key => $range) {
                 $bpAllStockData[] = $this->_api->getProductsAvailability($range);
            }
            
            $msg  = 'Brightpearl All Stock ('.count($bpAllStockData).') Fetch Time '.date('Y-m-d H:i:s');
            $this->_logManager->recordLog($msg, "Stock Reconciliation", "Stock Reconciliation");
             
            foreach ($bpAllStockData as $key => $items) {
                if (count($items) > 0) {
                    foreach ($items as $bpid => $value) {
                        $whQtyArray = [];
                        foreach ($value['warehouses'] as $wid => $warehouse) {
                            $onHandQty         = $warehouse['onHand'];
                             $intransitQty = 0 ;
                            $intrsKey = $bpid.'_'.$wid;
                            if (array_key_exists($intrsKey, $stocktTransferDataArray)) {
                                 $intransitQty = $stocktTransferDataArray[$intrsKey];
                            }
                            
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
                                }
                            }
                            $finalWid        = implode(",", $finalWidArray);
                            $finalWname        = implode(",", $finalWnameArray);
                            $finalQty         = intval($finalQty);
                            
                            // ---------------- new code here ------------------
                            $tmpKey = $locId.'_'.$bpid;
                            if (array_key_exists($tmpKey, $alreadyInsertedData)) {
                                $alreadyInsertedData[$tmpKey]['bp_qty']         = $finalQty;
                                $alreadyInsertedData[$tmpKey]['warehouse_id']     = $finalWid;
                                $alreadyInsertedData[$tmpKey]['warehouses']     = $finalWname;
                                $alreadyInsertedData[$tmpKey]['diff']            = $alreadyInsertedData[$tmpKey]['mgt_qty'] - $finalQty;
                            }
                        }
                    }
                }
            }
            $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
            $this->_connection->truncateTable($bsr);
             $this->_connection->insertMultiple($bsr, $alreadyInsertedData);
            $msg = ' Final End at '.date('Y-m-d H:i:s');
            $this->_logManager->recordLog($msg, "Stock Reconciliation", "Stock Reconciliation");
        }
        
        $this->refreshBundleStock();
        
        return true;
    }

    public function getStocktTransferData()
    {
        
        $stocktransferArray = [];
        $stocktransfer        = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $collection         = $stocktransfer->create()->getCollection()->addFieldToFilter('status', $this->goReleaseState);
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $targetwarehouseid     = $item->getTargetwarehouseid();
                $productid             = $item->getProductid();
                $quantity             = $item->getQuantity();
                $key                 = $productid.'_'.$targetwarehouseid;
                if (array_key_exists($key, $stocktransferArray)) {
                    $stocktransferArray[$key] += $quantity ;
                } else {
                    $stocktransferArray[$key] = $quantity ;
                }
            }
        }
        return $stocktransferArray;
    }
    
    public function getBpIdCollectionNew()
    {
        
        $collection = [];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $stock_id = [];
         $stock_id = $this->_inventoryWarehouseMapping->create()->getCollection()->addFieldToSelect('mgt_location')->getColumnValues('mgt_location');
        
        if (count($stock_id) > 0) {
            $isi = $this->_resource->getTableName('inventory_source_item');
            $cpe = $this->_resource->getTableName('catalog_product_entity');
            $bbw = $this->_resource->getTableName('bsitc_brightpearl_warehouse');
            $bbbi = $this->_resource->getTableName('bsitc_brightpearl_bpitems');
 
            $bsr = $this->_resource->getTableName('bsitc_stock_reconciliation');
            $this->_connection->truncateTable($bsr);
            
            $selectColumns= [    "isi.source_item_id", "isi.source_code", "isi.sku", "isi.quantity", "isi.status", "cpe.entity_id", "cpe.sku", "cpe.type_id", "bbw.bp_warehouse", "bbw.mgt_location", "bbbi.bp_id", "bbbi.bp_ptype"];
                     
            $sql = $this->_connection->select()->from(["isi"=>$isi], [])
            ->joinLeft(["cpe"=>$cpe], "isi.sku = cpe.sku", [])
            ->joinLeft(["bbw"=>$bbw], "isi.source_code = bbw.mgt_location", [])
            ->join(["bbbi"=>$bbbi], "cpe.sku = bbbi.bp_sku", $selectColumns)
            ->where('isi.source_code IN (?)', $stock_id);
             
            $results = $this->_connection->fetchAll($sql);
            
            // echo '<pre>'; print_r($results); echo '</pre>'; die;
               
            foreach ($results as $item) {
                $tmpKey = $item['source_code'].'_'.$item['bp_id'];
                $bp_id = $item['bp_id'];
                
                $row                     = [];
                $row['warehouse_id']     = $item['bp_warehouse'];
                $row['warehouses']         = '';
                $row['location_id']     = $item['source_code'];
                $row['sku']             = $item['sku'];
                $row['bp_id']             = $item['bp_id'];
                $row['mgt_qty']         = intval($item['quantity']);
                $row['bp_qty']             = '';
                $row['diff']             = '';
                $row['bp_ptype']         = $item['bp_ptype'];
                 
                $collection['A'][$bp_id] =  $item['bp_id'];
                $collection['B'][$tmpKey] = $row;
            }
        }
        return $collection;
    }
}
