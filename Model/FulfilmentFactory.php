<?php

namespace Bsitc\Brightpearl\Model;

class FulfilmentFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    public $_storeManager;
    public $_scopeConfig;
    public $_logManager;
    public $_api;
    public $_queuestatus;
    public $_resultstatus;
    public $_bpshipping;
    public $_mgtorder;
    public $_stf;
    public $_productRepository;
    public $_inventory;
     
    public $pendingState;
    public $processingState;
    public $completeState;
    public $errorState;
    public $goReleaseState;
    public $goProcessingState;
    public $itReceivedState;
    public $goErrorState;
    public $_date;
        
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
        \Bsitc\Brightpearl\Model\Bpshipping $bpshipping,
        \Bsitc\Brightpearl\Model\Mgtorder $mgtorder,
        \Bsitc\Brightpearl\Model\StocktransferFactory $stf,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Bsitc\Brightpearl\Helper\Inventory $inventory,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager
    ) {
        $this->_date             = $date;
        $this->_objectManager     = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_queuestatus     = $queuestatus;
        $this->_resultstatus     = $resultstatus;
        $this->_bpshipping         = $bpshipping;
        $this->_mgtorder         = $mgtorder;
        $this->_stf             = $stf;
        $this->_productRepository = $productRepository;
        $this->_inventory         = $inventory;
        
         $queue_status            = $this->_queuestatus->getQueueOptionArray();
        $this->pendingState        = $queue_status['Pending'];
        $this->processingState    = $queue_status['Processing'];
        $this->completeState    = $queue_status['Completed'];
        $this->errorState        = $queue_status['Error'];
        $this->goReleaseState        = $queue_status['GO_Release'];
        $this->goProcessingState    = $queue_status['IT_Received'];
        $this->itReceivedState        = $queue_status['GO_Processing'];
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Fulfilment', $arguments, false);
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
    
    public function goodsOutNoteModifiedShipped($data)
    {
        $finalData = [];
        $finalData['gon_id'] = $data['id'];
        $finalData['status'] = $this->pendingState;
        $finalData['created_at'] = $data['raisedOn'];
        $this->addRecord($finalData);
        return true;
    }
    
    public function processQueue()
    {
        if ($this->_api->authorisationToken) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            
            $this->updateStuckQueueRecord();  // ------- update stuck queue records
            
            if ($this->checkQueueProcessingStatus()) {  // ------- check previous cron running or not
                 return '';
            }

            $bpShippinngMethodArray = $this->_bpshipping->getBpShippinngOptionArray();
            
             $collection =   $this->create()->getCollection();
            $collection->addFieldToFilter('status', ['eq'=>$this->pendingState]);
            // -------------- filter good out note here ---------------
            if (count($collection) > 0) {
                /* --------  update status in processing state  ---------------*/
                foreach ($collection as $item) {
                    $item->setState($this->processingState)->save();
                }
                
                foreach ($collection as $item) {
                    $goodOutNoteId         = $item->getGonId();
                    $getGoodsOutNote     = $this->_api->getGoodsOutNote($goodOutNoteId);
                    
                    try {
                        if (array_key_exists("response", $getGoodsOutNote) and count($getGoodsOutNote['response'])> 0) {
                             $orderId     = $getGoodsOutNote['response'][$item->getGonId()]['orderId'];
                             $transfer     = $getGoodsOutNote['response'][$item->getGonId()]['transfer'];
                             $gonStatus     = $getGoodsOutNote['response'][$item->getGonId()]['status']['shipped'];
                             
                            if ($orderId == 0 and $transfer == 1) {
                                // $this->processStockTransferItem($item, $getGoodsOutNote);
                                if ($gonStatus != 1) {
                                    $this->processStockTransferItem($item, $getGoodsOutNote);
                                } else {
                                    $updateDataArray     = [];
                                    $updateDataArray ['status'] = $this->completeState ;
                                    $updateDataArray ['json'] =  ' Error # '.json_encode($getGoodsOutNote, true) ;
                                    $this->updateRecord($item->getId(), $updateDataArray);
                                    $msg = 'Stock transfer process only for created Goods Out Not, This GON shipped status is '.$gonStatus;
                                    $this->_logManager->recordLog($msg, "Stock transfer", "Goodoutnoteid:".$goodOutNoteId);
                                }
                            } else {
								if ($gonStatus == 1){
									$this->processFulfilmentItem($item, $getGoodsOutNote, $bpShippinngMethodArray);
 								}else{
									$updateDataArray     = [];
									$updateDataArray ['status'] = $this->errorState ;
									$updateDataArray ['json'] =  ' Not Shipped Yet # '.json_encode($getGoodsOutNote, true) ;
									$this->updateRecord($item->getId(), $updateDataArray);
								}
                            }
                        } else {
                            // --------------- record log data ---------------------
                            $updateDataArray     = [];
                            $updateDataArray ['status'] = $this->errorState ;
                            $updateDataArray ['json'] =  ' Error # '.json_encode($getGoodsOutNote, true) ;
                            $this->updateRecord($item->getId(), $updateDataArray);
                        }
                    } catch (\Exception $e) {
                            $this->_logManager->recordLog($e->getMessage(), $title = "Fulfilment", $category = "Goodoutnoteid:".$goodOutNoteId);
                    }
                }
            }
        }
    }
    
    public function processFulfilmentItem($item, $getGoodsOutNote, $bpShippinngMethodArray)
    {
        $updateDataArray     = [];
        $goodOutNoteId         = $item->getGonId();
        if (array_key_exists("response", $getGoodsOutNote)) {
            $bpGon     = $getGoodsOutNote['response'][$item->getGonId()];
            $bpOrderId        = $bpGon['orderId'];
            /* -------- get order brightpearl detail by order id  ---------------*/
            $getBpOrder        = $this->_api->orderById($bpOrderId);
            $bpOrder         = $getBpOrder['response'][0];
            $orderIncrementId = trim($bpOrder['reference']);
             
            /* -------- update recored in report ----------------*/
            $updateDataArray ['so_order_id'] = $bpOrderId ;
            $updateDataArray ['mgt_order_id'] = $orderIncrementId ;
            $this->updateRecord($item->getId(), $updateDataArray);
            
            /* -------- check is this magetno order or not ------------------- */
            $checkMgtOrder = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByAttribute('increment_id', $orderIncrementId);
            if ($checkMgtOrder->getId() == "") {
                $this->removeRecord($item->getId());
                return true;
            }
             /* -------- check is this magetno order or not ------------------- */
            

            $orderItemArray = [];
            foreach ($bpOrder['orderRows'] as $rowId => $orderItem) {
                if (array_key_exists("productSku", $orderItem)) {
                    $productId  = $orderItem['productId'];
                    $productSku = $orderItem['productSku'];
                    $orderItemArray[$productId ] = $productSku;
                }
            }
            
            /* -------- Create Shipping Item Array ---------------*/
            $bpShippingProductArray = [];
            foreach ($bpGon['orderRows'] as $rowId => $product) {
                $sku    = $orderItemArray[$product[0]['productId']];
                $bpShippingProductArray[$sku]     = $product[0]['quantity'];
            }
            /* -------- Create Tracking Array ---------------*/
            $bpShippingTrackingArray = [];
            $traking = $bpGon['shipping'];
            if (array_key_exists("shippingMethodId", $traking)) {
                $title     = $bpShippinngMethodArray[$traking['shippingMethodId']];
                $number = @$traking['reference'];
                if ($number and trim($number)!="") {
                    $bpShippingTrackingArray[] = ['carrier_code'=>$title,'title' => $title,'number'=>$number];
                }
            }
            /* -------- Create shipment in magento ---------------*/
            $mgt_shipment_id  = $this->_mgtorder->createShipment($orderIncrementId, $bpShippingProductArray, $bpShippingTrackingArray);
            /* -------- update recored in report ----------------*/
            if ($mgt_shipment_id > 0) {
                $updateDataArray ['mgt_shipment_id'] = $mgt_shipment_id ;
                $updateDataArray ['mgt_shipment_status'] = 1 ;
                $updateDataArray ['status'] = $this->completeState ;
                $this->updateRecord($item->getId(), $updateDataArray);
            } else {
                $updateDataArray ['mgt_shipment_status'] = 0 ;
                $updateDataArray ['status'] = $this->errorState ;
                $this->updateRecord($item->getId(), $updateDataArray);
            }
        } else {
            // --------------- record log data ---------------------
            $updateDataArray ['status'] = $this->errorState ;
            $updateDataArray ['json'] =  ' Error # '.json_encode($getGoodsOutNote, true) ;
            $this->updateRecord($item->getId(), $updateDataArray);
        }
    }

    public function processStockTransferItem($item, $getGoodsOutNote)
    {
        $updateFulfillmentArray     = [];
        $goodOutNoteId         = $item->getGonId();
        if (array_key_exists("response", $getGoodsOutNote)) {
            $this->_logManager->recordLog(json_encode($getGoodsOutNote, true), "Start Stock Transfer", "stock transfer");
            $bpGon    = $getGoodsOutNote['response'][$item->getGonId()];
            $transferRows  = $bpGon['transferRows'];
            foreach ($transferRows as $row) {
                $updateDataArray     = [];
                $updateDataArray['goodsoutnoteid']             =  $goodOutNoteId;
                $updateDataArray['fromwarehouseid']         =  $bpGon['warehouseId'];
                $updateDataArray['targetwarehouseid']         =  $bpGon['targetWarehouseId'];
                $updateDataArray['stocktransferid']         =  $bpGon['stockTransferId'];
                $updateDataArray['shippedstatus']             =  $bpGon['status']['shipped'];
                $updateDataArray['createdby']                 =  $bpGon['createdBy'];
                 $updateDataArray['productid']                 =  $row['productId'];
                 $updateDataArray['productsku']                 =  '';
                $updateDataArray['quantity']                 =  $row['quantity'];
                $updateDataArray['locationid']                 =  $row['locationId'];
                $updateDataArray['created_at']                 =  date("Y-m-d h:i:s");
                $updateDataArray['status']                     =  $this->pendingState;
                
                $stfId  = $this->_stf->addRecord($updateDataArray, 'return_id');
                // $this->_logManager->recordLog( $stfId , " stfId  stock transfer", "stock transfer" );
                
                // -------------------- search goods movement -------------------
                    $filter = [];
                    $filter['goodsNoteId']             =  $updateDataArray['goodsoutnoteid'];
                    $filter['warehouseId']             =  $updateDataArray['targetwarehouseid'];
                    $filter['productId']             =  $updateDataArray['productid'];
                    $filter['quantity']             =  $updateDataArray['quantity'];
                    $filter['goodsNoteTypeCode']     = 'GO';
                    $filter['isQuarantine']            = 'true';
                    $serchResultGoddMovement         = $this->_api->searchGoodsMovement($filter);
                if (count($serchResultGoddMovement)>0) {
                    $updateDataArray['goodsmovementid']     =  $serchResultGoddMovement[0]['goodsMovementId'];
                    $updateDataArray['batchid']                =   $serchResultGoddMovement[0]['batchId'];
                    $this->_stf->updateRecord($stfId, $updateDataArray) ;
                }
                 // --------------------------------------------
                
                $targetwarehouseid    = $bpGon['targetWarehouseId'];
                $bpPid                = $row['productId'];
                 $onhandqty            = $row['quantity'];
                $bpProductResponse    =  $this->_api->getProductById($bpPid);
                if (array_key_exists("response", $bpProductResponse)) {
                    if (count($bpProductResponse['response'])>0) {
                        $bpProduct = $bpProductResponse['response']['0'];
                        $sku = trim($bpProduct['identity']['sku']);
                        $updateDataArray['productsku'] =  $sku;
                        //  get($sku, $editMode = false, $storeId = null, $forceReload = false)
                        $product = $this->_productRepository->get($sku, false, 0);
                        if ($product and $product->getSku() == $sku) {
                             $magetnoWarehouse    =  $this->_inventory->getMgtStoreId($targetwarehouseid); // store id from warehouse id
                            if (count($magetnoWarehouse) > 0) {
                                $mgtWarehouseid = $magetnoWarehouse['mgtlocation'];
                                  $onhandqty  =  $onhandqty + $this->_inventory->getMsiInventory($sku, $mgtWarehouseid);
                                $this->_inventory->UpdateMsiInventory($sku, $onhandqty, $mgtWarehouseid);
                                  $updateDataArray['productsku']     =  $sku;
                                 $updateDataArray['status']        =  $this->goReleaseState;
                                $this->_stf->updateRecord($stfId, $updateDataArray) ;
                                $this->removeRecord($item->getId());
                            } else {
                                $msg = 'Unable to update stock due to BP Warehouse (#'.$targetwarehouseid.') not found in Magento';
                                $this->_logManager->recordLog($msg, "stock transfer", "stock transfer");
                                $updateDataArray['status']        =  $this->errorState;
                                $this->_stf->updateRecord($stfId, $updateDataArray) ;
                            }
                        } else {
                            $msg = 'Product not exist with SKU'.$productsku;
                            $this->_logManager->recordLog($msg, "stock transfer", "stock transfer");
                            $updateDataArray['status']        =  $this->errorState;
                            $this->_stf->updateRecord($stfId, $updateDataArray) ;
                        }
                    } else {
                           $msg = 'Product not found on Brightpearl #'.$bpPid;
                           $this->_logManager->recordLog($msg, "stock transfer", "stock transfer");
                           $updateDataArray['status']        =  $this->errorState;
                           $this->_stf->updateRecord($stfId, $updateDataArray) ;
                    }
                } else {
                    $msg = 'Unable to get product info from Brightpearl #'.$bpPid;
                    $this->_logManager->recordLog($msg, "stock transfer", "stock transfer");
                    $updateDataArray['status']        =  $this->errorState;
                    $this->_stf->updateRecord($stfId, $updateDataArray) ;
                }
            }
        } else {
            // --------------- record log data ---------------------
             $msg = ' Error # '.json_encode($getGoodsOutNote, true) ;
            $this->_logManager->recordLog($msg, "stock transfer", "stock transfer");
            $updateFulfillmentArray ['status'] = $this->errorState ;
            $updateFulfillmentArray ['json'] =  ' Error # '.json_encode($getGoodsOutNote, true) ;
            $this->updateRecord($item->getId(), $updateFulfillmentArray);
        }
    }

 
    public function updateStuckQueueRecord()
    {
            $adminHours = 0;
        if (!$adminHours) {
            $adminHours = 2;
        }
          $collection = $this->create()->getCollection();
          $collection->addFieldToFilter('status', ['eq'=>$this->processingState]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $date_a = $this->_date->date($item->getUpdatedAt());
                $date_b = $this->_date->date();
                $diff = $date_a->diff($date_b)->format('%i');
                if ($diff >= $adminHours) {
                    $item->setState($this->pendingState)->save();
                }
            }
        }
          return true;
    }

    public function checkQueueProcessingStatus()
    {
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', [ 'eq'=>$this->processingState ]);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }
}
