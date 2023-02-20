<?php

namespace Bsitc\Brightpearl\Model;

class PaymentcaptureFactory
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
    public $_mgtorder;
    
    public $pendingState;
    public $processingState;
    public $completeState;
    public $errorState;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Bsitc\Brightpearl\Model\Resultstatus $resultstatus,
        \Bsitc\Brightpearl\Model\Mgtorder $mgtorder,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_queuestatus     = $queuestatus;
        $this->_resultstatus     = $resultstatus;
        $this->_mgtorder         = $mgtorder;
        
        $queue_status            = $this->_queuestatus->getQueueOptionArray();
        $this->pendingState        = $queue_status['Pending'];
        $this->processingState    = $queue_status['Processing'];
        $this->completeState    = $queue_status['Completed'];
        $this->errorState        = $queue_status['Error'];
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Paymentcapture', $arguments, false);
    }
    
    public function addRecord($row, $returnId = 'flase')
    {
        if (count($row)>0) {
            $record = $this->create();
            $record->setData($row);
            $record->save();
        }
        if ($returnId == true) {
            return $record->getId();
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
    
    public function goodsOutNoteModifiedPicking($data)
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

             $collection =   $this->create()->getCollection();
            $collection->addFieldToFilter('status', ['eq'=>$this->pendingState]);
            if (count($collection) > 0) {
                /* --------  update status in processing state  ---------------*/
                foreach ($collection as $item) {
                    $item->setState($this->processingState)->save();
                }
                foreach ($collection as $item) {
                    $item->setState($this->errorState)->save();
                    
                    $updateDataArray     = [];
                    $goodOutNoteId         = $item->getGonId();
                    $getGoodsOutNote     = $this->_api->getGoodsOutNote($goodOutNoteId);
                    if (array_key_exists("response", $getGoodsOutNote)) {
                        try {
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
                         
                            /* -------- Capture payment in magento ---------------*/
                            $mgt_invoice_state  = $this->_mgtorder->capturePayment($orderIncrementId);
                         
                            /* -------- update recored in report ----------------*/
                            if ($mgt_invoice_state > 1) {
                                 $updateDataArray ['mgt_payment_status'] = 1 ;
                                $updateDataArray ['status'] = $this->completeState ;
                                $this->updateRecord($item->getId(), $updateDataArray);
                            } else {
                                $updateDataArray ['mgt_payment_status'] = 0 ;
                                $updateDataArray ['status'] = $this->errorState ;
                                $this->updateRecord($item->getId(), $updateDataArray);
                            }
                        } catch (\Exception $e) {
                             $this->_logManager->recordLog($e->getMessage(), $title = "Paymentcapture", $category = "GoodsOutNoteId".$goodOutNoteId);
                        }
                    } else {
                        // --------------- record log data ---------------------
                         $updateDataArray ['status'] = $this->errorState ;
                         $updateDataArray ['json'] =  ' Error # '.json_encode($getGoodsOutNote, true) ;
                         $this->updateRecord($item->getId(), $updateDataArray);
                    }
                }
            }
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
