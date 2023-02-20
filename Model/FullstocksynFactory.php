<?php

namespace Bsitc\Brightpearl\Model;

class FullstocksynFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    public $_logManager;
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
        \Bsitc\Brightpearl\Model\Api $api,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_scopeConfig        = $scopeConfig;
        $this->_resource        = $resource;
        $this->_connection        = $this->_resource->getConnection();
        $this->_queuestatus     = $queuestatus;
        
        $queue_status                = $this->_queuestatus->getQueueOptionArray();
        $this->goReleaseState        = $queue_status['GO_Release'];
        $this->goProcessingState    = $queue_status['GO_Processing'];
        $this->itReceivedState        = $queue_status['IT_Received'];
        $this->goErrorState            = $queue_status['GO_Error'];
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Fullstocksyn', $arguments, false);
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
    
    
    public function refreshReport()
    {
        
        $bpItemsFactory             = $this->_objectManager->create('Bsitc\Brightpearl\Model\BpitemsFactory');
        $inventoryWarehouseMapping     = $this->_objectManager->create('Bsitc\Brightpearl\Model\WarehouseFactory');
        $stocktransfer                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $inventoryHelper            = $this->_objectManager->create('Bsitc\Brightpearl\Helper\Inventory');
        $allwarehouseObj            = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
         $allwarehouseArray             = $allwarehouseObj->toArray();
        
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $stock_id = [];
         $stock_id = $inventoryWarehouseMapping->create()->getCollection()->addFieldToSelect('mgt_location')->getColumnValues('mgt_location');
         
        if (count($stock_id) > 0) {
            $isi = $this->_resource->getTableName('inventory_source_item');
            $cpe = $this->_resource->getTableName('catalog_product_entity');
            $bbw = $this->_resource->getTableName('bsitc_brightpearl_warehouse');
            $bbbi = $this->_resource->getTableName('bsitc_brightpearl_bpitems');
            
            $bsr = $this->_resource->getTableName('bsitc_fullstock_syn');
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
                $row['mgt_id']             = $item['entity_id'];
                $row['mgt_qty']         = intval($item['quantity']);
                $row['bp_id']             = $item['bp_id'];
                $row['bp_ptype']         = $item['bp_ptype'];
                 $finalResults[$tmpKey]     = $row ;
            }
            
            $bsr = $this->_resource->getTableName('bsitc_fullstock_syn');
            $this->_connection->insertMultiple($bsr, $finalResults);
        }
        return true;
    }

    public function getBpIdCollection()
    {

        $collection = [];
        
        $bpItemsFactory             = $this->_objectManager->create('Bsitc\Brightpearl\Model\BpitemsFactory');
        $inventoryWarehouseMapping     = $this->_objectManager->create('Bsitc\Brightpearl\Model\WarehouseFactory');
        $stocktransfer                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $inventoryHelper            = $this->_objectManager->create('Bsitc\Brightpearl\Helper\Inventory');
        $allwarehouseObj            = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
         $allwarehouseArray             = $allwarehouseObj->toArray();
        
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $stock_id = [];
         $stock_id = $inventoryWarehouseMapping->create()->getCollection()->addFieldToSelect('mgt_location')->getColumnValues('mgt_location');
         
        if (count($stock_id) > 0) {
            $isi = $this->_resource->getTableName('inventory_source_item');
            $cpe = $this->_resource->getTableName('catalog_product_entity');
            $bbw = $this->_resource->getTableName('bsitc_brightpearl_warehouse');
            $bbbi = $this->_resource->getTableName('bsitc_brightpearl_bpitems');
             
            $selectColumns= [    "isi.source_item_id", "isi.source_code", "isi.sku", "isi.quantity", "isi.status", "cpe.entity_id", "cpe.sku", "cpe.type_id", "bbw.bp_warehouse", "bbw.mgt_location", "bbbi.bp_id", "bbbi.bp_ptype"];
            
            $sql = $this->_connection->select()->from(["isi"=>$isi], [])
            ->joinLeft(["cpe"=>$cpe], "isi.sku = cpe.sku", [])
            ->joinLeft(["bbw"=>$bbw], "isi.source_code = bbw.mgt_location", [])
            ->join(["bbbi"=>$bbbi], "cpe.sku = bbbi.bp_sku", $selectColumns)
            ->where('isi.source_code IN (?)', $stock_id);
 
            $results = $this->_connection->fetchAll($sql);
            foreach ($results as $item) {
                $bp_id = $item['bp_id'];
                $collection[$bp_id] =  $bp_id ;
            }
        }

        return $collection;
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
        
        $bpItemsFactory             = $this->_objectManager->create('Bsitc\Brightpearl\Model\BpitemsFactory');
        $inventoryWarehouseMapping     = $this->_objectManager->create('Bsitc\Brightpearl\Model\WarehouseFactory');
        $stocktransfer                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $inventoryHelper            = $this->_objectManager->create('Bsitc\Brightpearl\Helper\Inventory');
        $allwarehouseObj            = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
         $allwarehouseArray             = $allwarehouseObj->toArray();
        
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $stock_id = [];
         $stock_id = $inventoryWarehouseMapping->create()->getCollection()->addFieldToSelect('mgt_location')->getColumnValues('mgt_location');
         
        if (count($stock_id) > 0) {
            $isi = $this->_resource->getTableName('inventory_source_item');
            $cpe = $this->_resource->getTableName('catalog_product_entity');
            $bbw = $this->_resource->getTableName('bsitc_brightpearl_warehouse');
            $bbbi = $this->_resource->getTableName('bsitc_brightpearl_bpitems');
             
            $selectColumns= [    "isi.source_item_id", "isi.source_code", "isi.sku", "isi.quantity", "isi.status", "cpe.entity_id", "cpe.sku", "cpe.type_id", "bbw.bp_warehouse", "bbw.mgt_location", "bbbi.bp_id", "bbbi.bp_ptype"];
                     
            $sql = $this->_connection->select()->from(["isi"=>$isi], [])
            ->joinLeft(["cpe"=>$cpe], "isi.sku = cpe.sku", [])
            ->joinLeft(["bbw"=>$bbw], "isi.source_code = bbw.mgt_location", [])
            ->join(["bbbi"=>$bbbi], "cpe.sku = bbbi.bp_sku", $selectColumns)
            ->where('isi.source_code IN (?)', $stock_id);
             
            $results = $this->_connection->fetchAll($sql);
            foreach ($results as $item) {
                $bp_id         = $item['bp_id'];
                $sku         = $item['sku'];
                $bp_ptype     = $item['bp_ptype'];
                $product_id = $item['entity_id'];
                
                $collection['A'][$bp_id]                 =  $bp_id ;
                $collection['B'][$bp_id]['bp_id']         =  $bp_id ;
                $collection['B'][$bp_id]['bp_sku']         =  $sku ;
                $collection['B'][$bp_id]['bp_ptype']     =  $bp_ptype ;
                $collection['B'][$bp_id]['product_id']     =  $product_id ;
            }
        }

        return $collection;
    }
    
    
    public function synchronize()
    {
        
         $bpItemsFactory             = $this->_objectManager->create('Bsitc\Brightpearl\Model\BpitemsFactory');
        $inventoryWarehouseMapping     = $this->_objectManager->create('Bsitc\Brightpearl\Model\WarehouseFactory');
        $stocktransfer                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $inventoryHelper            = $this->_objectManager->create('Bsitc\Brightpearl\Helper\Inventory');
        $allwarehouseObj            = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
         $allwarehouseArray             = $allwarehouseObj->toArray();
        
        $stocktTransferDataArray    = $this->getStocktTransferData();
        
        $bfs = $this->_resource->getTableName('bsitc_fullstock_syn');
        $this->_connection->truncateTable($bfs);
        
        if ($inventoryHelper->getConfig('bpconfiguration/bpinventory/fullinventorysyn')) {
            $warehouseMapping = [];
            $warehouseMappingCollection = $inventoryWarehouseMapping->create()->getCollection();
            foreach ($warehouseMappingCollection as $item) {
                $warehouseMapping[$item->getMgtLocation()][] = $item->getBpWarehouse();
            }
            if ($warehouseMapping > 0) {
                 $getUris = [];
                $collectionAb         = $this->getBpIdCollectionNew();
                $collection         = $collectionAb['A'];
                $allBpItemsArray     = $collectionAb['B'];
                
                $collectionRangeString =  $this->getRangeString($collection, '200');
                $msg  = 'Total Chunks for Api Call '.count($collectionRangeString);
                $this->_logManager->recordLog($msg, "Full Stock Syn", "Full Stock Syn");
                 
                if (count($collectionRangeString) > 0) {
                    $bpAllStockData = [];
                    foreach ($collectionRangeString as $key => $range) {
                        $bpAllStockData[] = $this->_api->getProductsAvailability($range);
                    }
                     
                      $msg  = 'Brightpearl All Stock ('.count($bpAllStockData).') Fetch Time '.date('Y-m-d H:i:s');
                    $this->_logManager->recordLog($msg, "Full Stock Syn", "Full Stock Syn");
                    
                    $finalResults = [];
                    foreach ($bpAllStockData as $key => $items) {
                        if (count($items) > 0) {
                            foreach ($items as $bpid => $value) {
                                $mgtSku = '';
                                $pid     = '';
                                $bp_ptype = 0;
                                 
                                $search = $allBpItemsArray[$bpid] ; /* get sku from brgihtpeal id */
                                if ($search) {
                                    $mgtSku     = $search['bp_sku'];
                                    $bp_ptype     = $search['bp_ptype'];
                                    $pid         = $search['product_id'];
                                }
                                
                                if ($mgtSku!="" and $pid > 0) {
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
                                        $finalQty             = 0;
                                        $finalWidArray         = [];
                                        $finalWnameArray     = [];
                                        foreach ($locations as $location) { // locatin meand warehouse id of brightpearl
                                            $finalWidArray[]     = $location;
                                            $finalWnameArray[]     = $allwarehouseArray[$location];
                                            if (array_key_exists($location, $whQtyArray)) {
                                                $finalQty  += $whQtyArray[$location];
                                            }
                                        }

                                        $finalWid             = implode(",", $finalWidArray);
                                        $finalWname         = implode(",", $finalWnameArray);
                                        // $previousqty     = $inventoryHelper->getMagentoInventory($pid, $locId, $locId);
                                        $previousqty         = $inventoryHelper->getMsiInventory($mgtSku, $locId);
                                        
                                        $previousqty         =  intval($previousqty);
                                        $finalQty             =  intval($finalQty);
                                        if ($previousqty  != $finalQty) {
                                            // $inventoryHelper->InventoryUpdate($pid, $finalQty, $locId, $locId); // update inventory using helper class
                                            $result = $inventoryHelper->UpdateMsiInventory($mgtSku, $finalQty, $locId);
                                        }
          
                                        $row                    = [];
                                        $row['location_id']        = $locId;
                                        $row['warehouse_id']    = $finalWid;
                                        $row['warehouses']        = $finalWname;
                                        $row['bp_id']            = $bpid;
                                        $row['mgt_id']            = $pid;
                                        $row['sku']                = $mgtSku;
                                        $row['mgt_qty']            = intval($previousqty);
                                        $row['bp_qty']            = intval($finalQty);
                                        $row['bp_ptype']        = $bp_ptype;
                                         $tmpKey = $locId.'_'.$bpid;
                                        $finalResults[$tmpKey] = $row ;
                                    }
                                }
                            }
                        }
                    }
                    
                    $this->_connection->insertMultiple($bfs, $finalResults);
                    $msg = ' Simple stock updatation finished at '.date('Y-m-d H:i:s');
                    $this->_logManager->recordLog($msg, "Full Stock Syn", "Full Stock Syn");
                }
            }
            
              $this->synchronizeBundle();
              $msg = ' Synchronize Bundle stock finished at '.date('Y-m-d H:i:s');
              $this->_logManager->recordLog($msg, "Full Stock Syn", "Full Stock Syn");
        }
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

    public function synchronizeBundle()
    {
        
        $bpItemsFactory             = $this->_objectManager->create('Bsitc\Brightpearl\Model\BpitemsFactory');
        $inventoryWarehouseMapping     = $this->_objectManager->create('Bsitc\Brightpearl\Model\WarehouseFactory');
        $stocktransfer                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $inventoryHelper            = $this->_objectManager->create('Bsitc\Brightpearl\Helper\Inventory');
        $allwarehouseObj            = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
         $allwarehouseArray             = $allwarehouseObj->toArray();
 
        if ($inventoryHelper->getConfig('bpconfiguration/bpinventory/fullinventorysyn')) {
            $warehouseMapping = [];
            $warehouseMappingCollection = $inventoryWarehouseMapping->create()->getCollection();
            foreach ($warehouseMappingCollection as $item) {
                $warehouseMapping[$item->getMgtLocation()][] = $item->getBpWarehouse();
            }
      
            if ($warehouseMapping > 0) {
                $collection = $bpItemsFactory->create()->getCollection()->addFieldToFilter('bp_ptype', '1');
                $collection->addFieldToFilter('bp_sku', ['neq' => false]);
                if (count($collection) > 0) {
                    foreach ($collection as $item) {
                        $bpid = $item->getBpId();
                        $mgtSku = $item->getBpSku();
                        $bp_ptype = $item->getBpPtype();

                        $collection = $this->_api->getBundleAvailability($bpid);
                        if (array_key_exists('response', $collection)) {
                            $response = $collection['response'];
                            if (array_key_exists($bpid, $response)) {
                                $result = $response[$bpid];
                                $pid    = $this->getMgtProductId($mgtSku); /* get mgt id from sku */
                                if ($mgtSku!="" and $pid > 0) {
                                    $whQtyArray = [];
                                    foreach ($result['warehouses'] as $wid => $warehouse) {
                                        $onHandQty         = $warehouse['onHand'];
                                        $intransitQty     = $stocktransfer->getProductQtyBySku($bpid, $wid);
                                        if ($intransitQty > 0) {
                                            $onHandQty = $onHandQty + $intransitQty;
                                        }
                                        $whQtyArray[$wid] = $onHandQty;
                                    }
                                    
                                    //echo '<pre>bp whQtyArray '; print_r($whQtyArray); echo '</pre>';
                                    
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
                                        $finalWid             = implode(",", $finalWidArray);
                                        $finalWname         = implode(",", $finalWnameArray);
                                        //$previousqty         = $inventoryHelper->getMagentoInventory($pid, $locId, $locId);
										$previousqty         = $inventoryHelper->getMsiInventory($mgtSku, $locId);
                                        
                                        // $inventoryHelper->InventoryUpdate($pid, $finalQty, $locId, $locId); // update inventory using helper class
                                        $inventoryHelper->UpdateMsiInventory($mgtSku, $finalQty, $locId);
          
                                        $row                    = [];
                                        $row['location_id']        = $locId;
                                        $row['warehouse_id']    = $finalWid;
                                        $row['warehouses']        = $finalWname;
                                        $row['bp_id']            = $bpid;
                                        $row['mgt_id']            = $pid;
                                        $row['sku']                = $mgtSku;
                                        $row['mgt_qty']            = intval($previousqty);
                                        $row['bp_qty']            = intval($finalQty);
                                        $row['bp_ptype']        = $bp_ptype;
     
                                        $data    = $this->findMgtRecordNew($bpid, $locId);
                                        if ($data) {
                                            $id    = $data['id'];
                                            $this->updateRecord($id, $row);
                                        } else {
                                            $this->addRecord($row);
                                        }
                                        // echo '<pre>row '; print_r($row); echo '</pre>';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
        
    public function getMgtProductId($sku)
    {
        $pid = '';
        $cpe = $this->_resource->getTableName('catalog_product_entity');
        $sql = $this->_connection->select()->from(['cpe' => $cpe])
            ->where('sku = ?', $sku);
         $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $pid = $result['entity_id'];
            //$data = $result;
        }
        return $pid;
    }
    
    public function findMgtRecordNew($bpid, $location_id)
    {
        $data = '';
         $bfs = $this->_resource->getTableName('bsitc_fullstock_syn');
        $sql = $this->_connection->select()->from(['bfs' => $bfs])
            ->where('bp_id = ?', $bpid)
            ->where('location_id = ?', $location_id);
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $data = $result;
        }
        return $data;
    }
}
