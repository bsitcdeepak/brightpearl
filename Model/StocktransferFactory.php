<?php

namespace Bsitc\Brightpearl\Model;

class StocktransferFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $_date;
    protected $_objectManager;
    public $_storeManager;
    public $_scopeConfig;
    public $_logManager;
    public $_api;
    public $_queuestatus;
    public $_resultstatus;
    public $_inventory;
    public $_productRepository;
    
    public $pendingState;
    public $processingState;
    public $completeState;
    public $errorState;
    public $goReleaseState;
    public $goProcessingState;
    public $itReceivedState;
    public $goErrorState;
    

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Bsitc\Brightpearl\Model\Resultstatus $resultstatus,
        \Bsitc\Brightpearl\Helper\Inventory $Inventory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_queuestatus     = $queuestatus;
        $this->_resultstatus     = $resultstatus;
        $this->_inventory        = $Inventory;
        $this->_productRepository    = $productRepository;
         $this->_date                 = $date;
         
        $queue_status            = $this->_queuestatus->getQueueOptionArray();
        $this->pendingState        = $queue_status['Pending'];
        $this->processingState    = $queue_status['Processing'];
        $this->completeState    = $queue_status['Completed'];
        $this->errorState        = $queue_status['Error'];
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Stocktransfer', $arguments, false);
    }
    
    
    public function addRecord($row, $return_id = '')
    {
        if (count($row)>0) {
            $record = $this->create();
            $record->setData($row);
            $record->save();
        }
        
        if ($return_id) {
            return $record->getId();
        } else {
            return true;
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
    
    public function getProductQtyBySku($bpproductid, $targetwarehouseid)
    {
        $data = 0;
        $collection = $this->create()->getCollection()
        ->addFieldToFilter('productid', $bpproductid)
        ->addFieldToFilter('targetwarehouseid', $targetwarehouseid)
        ->addFieldToFilter('status', $this->goReleaseState);
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $data += $item['quantity'];
            }
        }
        return $data;
    }
    
     
    public function processQueue()
    {
        if ($this->_api->authorisationToken) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $this->updateStuckQueueRecord();  // ------- update stuck queue records
            if ($this->checkQueueProcessingStatus()) {  // ------- check previous cron running or not
                 return '';
            }
            $collection =   $this->create()->getCollection();
            $collection->addFieldToFilter('status', ['eq'=>$this->goReleaseState]);
            if (count($collection) > 0) {
                /* --------  update status in processing state  ---------------*/
                foreach ($collection as $item) {
                     $row     = [];
                    $row ['status'] = $this->goProcessingState ;
                    // $this->updateRecord($item->getId(), $row);
                }
                
                foreach ($collection as $item) {
                    // -------------------- search goods movement -------------------
                    $filter = [];
                    $filter['warehouseId']             =  $item->getTargetwarehouseid();
                    $filter['productId']             =  $item->getProductid();
                    $filter['quantity']             =  $item->getQuantity();
                    $filter['goodsNoteTypeCode']     = 'IT';
                    $filter['isQuarantine']            = 'false';
                //    $filter['batchId']                = $item->getBatchid();
                    $serchResultGoddMovement         = $this->_api->searchGoodsMovement($filter);
                    
                    if (count($serchResultGoddMovement) > 0) {
                        foreach ($serchResultGoddMovement as $sgmItem) {
                            if ($sgmItem['goodsNoteId'] > $item->getGoodsoutnoteid() and $sgmItem['productId'] == $item->getProductid() and $sgmItem['warehouseId'] == $item->getTargetwarehouseid() and $sgmItem['orderId'] == "") {
                                $res = $this->updateReceivedStockInWarehouse($item->getProductsku(), $item->getQuantity(), $item->getTargetwarehouseid());
                                if ($res) {
                                    $updateDataArray     = [];
                                    $updateDataArray ['status'] = $this->itReceivedState ;
                                    $this->updateRecord($item->getId(), $updateDataArray);
                                    break;
                                }
                            } else {
                                $msg = 'Not found any stock transfer received for GON ID #'.$item->getGoodsoutnoteid();
                                $this->_logManager->recordLog($msg, "Stock transfer received", "stock transfer");
                            }
                        }
                    }
                }
            }
            // -----------  run inventory update here ---------
        }
    }
 
    public function updateReceivedStockInWarehouse($sku, $quantity, $targetwarehouseid)
    {
         $product = $this->_productRepository->get($sku, false, 0);
        if ($product and $product->getSku() == $sku) {
            $magetnoWarehouse    =  $this->_inventory->getMgtStoreId($targetwarehouseid); // store id from warehouse id
            if (count($magetnoWarehouse) > 0) {
                $mgtWarehouseid = $magetnoWarehouse['mgtlocation'];
                 $previousqty     = $this->_inventory->getMsiInventory($sku, $mgtWarehouseid);
                $onhandqty      = $previousqty - $quantity ;
                $this->_inventory->UpdateMsiInventory($sku, $onhandqty, $mgtWarehouseid);
                 $msg = 'Inventory in transit received for product'.$sku.' Old  qty = '.$previousqty.' decrease qty = '.$quantity .' in wahrehouse id '.$targetwarehouseid;
                $this->_logManager->recordLog($msg, "Stock transfer received", "stock transfer");
                return true;
            }
            return false;
        }
        return false;
    }
 
    
    public function updateStuckQueueRecord()
    {
            $adminHours = 0;
        if (!$adminHours) {
            $adminHours = 2;
        }
          $collection = $this->create()->getCollection();
          $collection->addFieldToFilter('status', ['eq'=>$this->goProcessingState]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $date_a = $this->_date->date($item->getUpdatedAt());
                $date_b = $this->_date->date();
                $diff = $date_a->diff($date_b)->format('%i');
                if ($diff >= $adminHours) {
                    $item->setState($this->goReleaseState)->save();
                }
            }
        }
          return true;
    }

    public function checkQueueProcessingStatus()
    {
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', [ 'eq'=>$this->goProcessingState ]);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }
}
