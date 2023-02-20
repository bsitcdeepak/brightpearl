<?php

namespace Bsitc\Brightpearl\Model;

class PaymentcapturequeueFactory
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
    public $_mgtorder;
    public $_pcf;
    
    public $pendingState;
    public $processingState;
    public $completeState;
    public $errorState;
    
    public $manuallySettledStat;
    public $ignoreStat;
    

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
        \Bsitc\Brightpearl\Model\Mgtorder $mgtorder,
        \Bsitc\Brightpearl\Model\PaymentcaptureFactory $paymentCaptureFactory,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager
    ) {
        $this->_date            = $date;
        $this->_objectManager = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_queuestatus     = $queuestatus;
        $this->_resultstatus     = $resultstatus;
        $this->_mgtorder         = $mgtorder;
        $this->_pcf                = $paymentCaptureFactory;
        $this->_scopeConfig     = $scopeConfig;
        
        $queue_status            = $this->_queuestatus->getQueueOptionArray();
        $this->pendingState        = $queue_status['Pending'];
        $this->processingState    = $queue_status['Processing'];
        $this->completeState    = $queue_status['Completed'];
        $this->errorState        = $queue_status['Error'];
        
        $this->manuallySettledStat    = $queue_status['Manually Settled'];
        $this->ignoreStat            = $queue_status['Ignore'];
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Paymentcapturequeue', $arguments, false);
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
    
    
    public function checkAlredyExits($bp_id)
    {
        
        $data = '';
        $data = $this->_pcf->findRecord('so_order_id', $bp_id);
        if ($data) {
            return true;
        }
         
        $collection = $this->create()->getCollection();
        $collection ->addFieldToFilter('bp_id', $bp_id);
        if ($collection->getSize()) {
            return true;
        }
        return false;
    }


    public function addWebhookRecord($data)
    {
        $id = trim($data['id']);
        if ($id) {
            $webhookdata = [];
            $webhookdata['bp_id']             = $data['id'];
            $webhookdata['account_code']    = $data['accountCode'];
            $webhookdata['resource_type']     = $data['resourceType'];
            $webhookdata['lifecycle_event'] = $data['lifecycleEvent'];
            $webhookdata['full_event']        = $data['fullEvent'];
            $webhookdata['status']             = $this->pendingState;
            $webhookdata['created_at']         = date('Y-m-d H:i:s');
             
            if (!$this->checkAlredyExits(trim($webhookdata['bp_id']))) {
                $this->addRecord($webhookdata);
            }
        }
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
                $updateCollection = $collection;
                foreach ($updateCollection as $item) {
                     $item->setStatus($this->processingState)->save();
                }
                $bpCaptureStatus        =  $this->getConfig('bpconfiguration/payment_capture_config/bp_capture_status');
                $bpCaptureUpdateStatus    =  $this->getConfig('bpconfiguration/payment_capture_config/bp_capture_update_status');
                $bpCaptureFailedStatus    =  $this->getConfig('bpconfiguration/payment_capture_config/bp_capture_failed_status');
                foreach ($collection as $item) {
                    $item->setStatus($this->errorState)->save();
                    $updateDataArray     = [];
                    $bpOrderId        = $item->getBpId();
                    /* -------- get order brightpearl detail by order id  ---------------*/
                    $getBpOrder        = $this->_api->orderById($bpOrderId);
                    if (array_key_exists("response", $getBpOrder)) {
                        try {
                            $bpOrder             = $getBpOrder['response'][0];
                            $orderIncrementId     = trim($bpOrder['reference']);
                             
                            $orderStatusId = $bpOrder['orderStatus']['orderStatusId'] ;
                            if ($orderStatusId == $bpCaptureStatus) {
                                /* -------- update recored in report ----------------*/
                                $updateDataArray ['so_order_id'] = $bpOrderId ;
                                $updateDataArray ['mgt_order_id'] = $orderIncrementId ;
                                $updateDataArray ['created_at'] = date('Y-m-d H:i:s');
                                $pdfId = $this->_pcf->addRecord($updateDataArray);
                                /* -------- Capture payment in magento ---------------*/
                                $mgt_invoice_state  = $this->_mgtorder->capturePayment($orderIncrementId);
                                $this->_logManager->recordLog('Capture Payment mgt_invoice_state '.$mgt_invoice_state, "mgt_invoice_state", "Order Id".$orderIncrementId);
                                /* -------- update recored in report ----------------*/
                                if ($mgt_invoice_state > 1) {
                                     $updateDataArray ['mgt_payment_status'] = 1 ;
                                    $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                    if ($bpCaptureUpdateStatus !="") {
                                        $bpOrderStatusArray = [];
                                        $bpOrderStatusArray['orderStatusId'] = $bpCaptureUpdateStatus;
                                        $this->_api->updateOrderStatus($bpOrderId, $bpOrderStatusArray) ;
                                        
                                        $updateDataArray ['status'] = $this->completeState ;
                                        $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                         $item->setStatus($this->completeState)->save();
                                    }
                                } else {
                                    $updateDataArray ['mgt_payment_status'] = 0 ;
                                     $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                    
                                    if ($bpCaptureFailedStatus !="") {
                                        $bpOrderStatusArray = [];
                                        $bpOrderStatusArray['orderStatusId'] = $bpCaptureFailedStatus;
                                        $this->_api->updateOrderStatus($bpOrderId, $bpOrderStatusArray) ;
                                        
                                        $updateDataArray ['status'] = $this->errorState ;
                                        $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                    }
                                }
                            } else {
                                $this->removeRecord($item->getId());
                            }
                        } catch (\Exception $e) {
                            $this->_logManager->recordLog($e->getMessage(), "Paymentcapture", "Order Id ".$bpOrderId);
                        }
                    } else {
                        // --------------- record log data ---------------------
                        $updateDataArray ['status'] = $this->errorState ;
                        //$updateDataArray ['json'] =  ' Error # '.json_encode($getBpOrder,true) ;
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
            $adminHours = 1;
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
    
    /**
     * Get store config
     */
    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
    
    
    public function cleanUnusedRecord()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $d                 = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $finalDate         = date('Y-m-d H:i:s', strtotime($d->gmtDate() . ' - 10 minute'));
        $pcfPendingCollection  = $this->_pcf->create()->getCollection();
        $pcfPendingCollection->addFieldToFilter('status', ['eq'=>$this->pendingState]);
        $pcfPendingCollection->addFieldToFilter('so_order_id', ['eq' => null]);
        $pcfPendingCollection->addFieldToFilter('updated_at', ['lteq' => $finalDate]);
        $pcfPending = [];
        if (count($pcfPendingCollection) > 0) {
            foreach ($pcfPendingCollection as $item) {
                $pcfPending['gon_id'][$item->getId()] = $item->getGonId();
                $item->delete();
            }
            $this->_logManager->recordLog(json_encode($pcfPending, true), "cleanUnusedRecord", 'Payment Capture');
        }
    }
    
   
    public function cleanFromQueueIfTheyInReport($so_order_id)
    {
        $data = $this->findRecord('bp_id', $so_order_id);
        if ($data) {
            $this->removeRecord($data->getId());
        }
    }


    public function processFailedPaymentCapture()
    {
        $this->cleanUnusedRecord();
        
        $this->retryQueueErrorStatus();
 
        if ($this->_api->authorisationToken) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $d                 = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
            $finalDate         = date('Y-m-d H:i:s', strtotime($d->gmtDate() . ' - 5 minute'));
            $collection     = $this->_pcf->create()->getCollection();
            $collection->addFieldToFilter('status', ['eq'=>$this->errorState]);
            $collection->addFieldToFilter('updated_at', ['lteq' => $finalDate]);
            if (count($collection) > 0) {
                $bpCaptureStatus        =  $this->getConfig('bpconfiguration/payment_capture_config/bp_capture_status');
                $bpCaptureUpdateStatus    =  $this->getConfig('bpconfiguration/payment_capture_config/bp_capture_update_status');
                $bpCaptureFailedStatus    =  $this->getConfig('bpconfiguration/payment_capture_config/bp_capture_failed_status');
                foreach ($collection as $item) {
                    // $item->setStatus($this->errorState)->save();
                    $updateDataArray     = [];
                    $bpOrderId            = $item->getSoOrderId();
                    $pdfId                 = $item->getId() ;
                    if ($bpOrderId) {
                        $this->cleanFromQueueIfTheyInReport($bpOrderId); /* --------- remove record form queue --------*/
                        /* -------- get order brightpearl detail by order id  ---------------*/
                        $getBpOrder        = $this->_api->orderById($bpOrderId);
                        if (array_key_exists("response", $getBpOrder)) {
                            if (array_key_exists("0", $getBpOrder['response'])) {
                                try {
                                    $bpOrder             = $getBpOrder['response'][0];
                                    $orderIncrementId     = trim($bpOrder['reference']);
                                    $orderStatusId         = $bpOrder['orderStatus']['orderStatusId'] ;
                                    $orderStatusName     = $bpOrder['orderStatus']['name'] ;
                                    $orderPaymentStatus    = $bpOrder['orderPaymentStatus'] ;
                                    if ($orderStatusId == $bpCaptureFailedStatus || $orderStatusId == $bpCaptureStatus) {
                                        /* -------- update recored in report ----------------*/
                                        $updateDataArray ['so_order_id'] = $bpOrderId ;
                                        $updateDataArray ['mgt_order_id'] = $orderIncrementId ;
                                        $updateDataArray ['updated_at'] = date('Y-m-d H:i:s');
                                        $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                        /* -------- Capture payment in magento ---------------*/
                                        $mgt_invoice_state  = $this->_mgtorder->capturePayment($orderIncrementId);
                                         /* -------- update recored in report ----------------*/
                                        if ($mgt_invoice_state > 1) {
                                            $updateDataArray ['mgt_payment_status'] = 1 ;
                                            $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                            if ($bpCaptureUpdateStatus !="") {
                                                $bpOrderStatusArray = [];
                                                $bpOrderStatusArray['orderStatusId'] = $bpCaptureUpdateStatus;
                                                $this->_api->updateOrderStatus($bpOrderId, $bpOrderStatusArray) ;
                                                $updateDataArray ['status'] = $this->completeState ;
                                                $updateDataArray ['updated_at'] = date('Y-m-d H:i:s');
                                                $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                                $item->setStatus($this->completeState)->save();
                                            }
                                        } else {
                                            $this->_logManager->recordLog('Retry Capture Payment failed due to Magetno Invoice status not ready to capture payment', "Mgt Order Id ".$orderIncrementId, "Payment Capture Retry");
                                            $updateDataArray ['mgt_payment_status'] = 0 ;
                                            $updateDataArray ['updated_at'] = date('Y-m-d H:i:s');
                                            $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                            if ($bpCaptureFailedStatus !="") {
                                                $bpOrderStatusArray = [];
                                                $bpOrderStatusArray['orderStatusId'] = $bpCaptureFailedStatus;
                                                $this->_api->updateOrderStatus($bpOrderId, $bpOrderStatusArray) ;
                                                $updateDataArray ['status'] = $this->errorState ;
                                                $updateDataArray ['updated_at'] = date('Y-m-d H:i:s');
                                                $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                            }
                                        }
                                    } elseif ($orderPaymentStatus == 'PAID') {
                                                $updateDataArray ['status'] = $this->manuallySettledStat ;
                                                $updateDataArray ['updated_at'] = date('Y-m-d H:i:s');
                                                $this->_pcf->updateRecord($pdfId, $updateDataArray);
                                                $this->_logManager->recordLog('Payment Manually Captured in Brightpearl '.$orderStatusName, "BP Order Id ".$bpOrderId, "Payment Capture Retry");
                                    } else {
                                        $this->_logManager->recordLog('Payment Capture Failed due to BP Order Status'.$orderStatusName, "BP Order Id ".$bpOrderId, "Payment Capture Retry");
                                    }
                                } catch (\Exception $e) {
                                    $this->_logManager->recordLog('Failed '.$e->getMessage(), "BP Order Id ".$bpOrderId, "Payment Capture Retry");
                                }
                            } else {
                                $this->_logManager->recordLog(json_encode('Failed '.$getBpOrder, true), "BP Order Id ".$bpOrderId, "Payment Capture Retry");
                            }
                        } else {
                            $this->_logManager->recordLog('Failed '.json_encode($getBpOrder, true), "BP Order Id ".$bpOrderId, "Payment Capture Retry");
                        }
                    }
                }
            }
        }
    }
     
    
    public function retryQueueErrorStatus()
    {
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', [ 'eq'=>$this->errorState ]);
        if (count($collection)) {
            foreach ($collection as $item) {
                 $so_order_id     = $item->getBpId();
                $data = $this->_pcf->create()->findRecord('so_order_id', $so_order_id);
                if ($data) {
                    $this->removeRecord($item->getId());  // remove from queue bcz it is in report
                } else {
                     $row = [];
                    $row ['so_order_id'] = $so_order_id;
                    $row ['status'] = $this->errorState;
                     $this->_pcf->create()->addRecord($row); // added in report with failed status to automatically retry from there
                    $this->removeRecord($item->getId());  // remove from queue
                }
            }
        }
    }
}
