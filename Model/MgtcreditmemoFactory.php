<?php

namespace Bsitc\Brightpearl\Model;

class MgtcreditmemoFactory
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
        \Bsitc\Brightpearl\Model\LogsFactory $logManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_scopeConfig        = $scopeConfig;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_queuestatus     = $queuestatus;
        $this->_resultstatus     = $resultstatus;
        
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Mgtcreditmemo', $arguments, false);
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
    
    
    public function processQueue()
    {
		
		
        return true;
        /*
        if($this->_api->authorisationToken)
        {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $this->updateStuckQueueRecord();  // ------- update stuck queue records

            if($this->checkQueueProcessingStatus())  // ------- check previous cron running or not
            {
                return '';
            }

             $collection =   $this->create()->getCollection();
            $collection->addFieldToFilter('status', array('eq'=>$this->pendingState));
            if( count($collection) > 0 )
            {
                // --------  update status in processing state  ---------------
                foreach($collection as $item){
                    $item->setState($this->processingState)->save();
                }

                foreach($collection as $item)
                {
                    $updateDataArray     = array();
                    $goodOutNoteId         = $item->getGonId();
                    $getGoodsOutNote     = $this->_api->getGoodsOutNote($goodOutNoteId);
                    if (array_key_exists("response",$getGoodsOutNote))
                    {
                        $bpGon     = $getGoodsOutNote['response'][$item->getGonId()];
                        $bpOrderId        = $bpGon['orderId'];
                        //  -------- get order brightpearl detail by order id  ---------------
                        $getBpOrder        = $this->_api->orderById($bpOrderId);
                        $bpOrder         = $getBpOrder['response'][0];
                        $orderIncrementId = trim($bpOrder['reference']);

                        // -------- update recored in report ----------------
                        $updateDataArray ['so_order_id'] = $bpOrderId ;
                        $updateDataArray ['mgt_order_id'] = $orderIncrementId ;
                        $this->updateRecord($item->getId(), $updateDataArray);

                        // -------- Post Credit Memo ---------------
                        // $mgt_shipment_id  = $this->_mgtorder->capturePayment($orderIncrementId);

                        // -------- update recored in report ----------------
                        if($mgt_shipment_id > 0 ){
                             $updateDataArray ['mgt_payment_status'] = 1 ;
                            $updateDataArray ['status'] = $this->completeState ;
                            $this->updateRecord($item->getId(), $updateDataArray);
                         }else{
                            $updateDataArray ['mgt_payment_status'] = 0 ;
                            $updateDataArray ['status'] = $this->errorState ;
                            $this->updateRecord($item->getId(), $updateDataArray);
                        }
                    }
                    else
                    {
                        // --------------- record log data ---------------------
                            $updateDataArray ['status'] = $this->errorState ;
                            $updateDataArray ['json'] =  ' Error # '.json_encode($getGoodsOutNote,true) ;
                            $this->updateRecord($item->getId(), $updateDataArray);
                    }
                }
            }
        }
        */
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
