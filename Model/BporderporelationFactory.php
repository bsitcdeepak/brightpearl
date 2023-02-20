<?php

namespace Bsitc\Brightpearl\Model;

class BporderporelationFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public $_date;

    public $_storeManager;
    
    public $_scopeConfig;
    
    public $_logManager;
    
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus
    ) {
        $this->_date            = $date;
        $this->_objectManager    = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_scopeConfig     = $scopeConfig;
        $this->_logManager         = $logManager;
        $this->_queuestatus        = $queuestatus;
        
        $queue_status            = $this->_queuestatus->getQueueOptionArray();
        $this->pendingState        = $queue_status['Pending'];
        $this->processingState    = $queue_status['Processing'];
        $this->completeState    = $queue_status['Completed'];
        $this->errorState        = $queue_status['Error'];
        $this->errorFailed        = $queue_status['Failed'];
        $this->errorNotPaid        = $queue_status['Not_Paid'];
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bporderporelation', $arguments, false);
    }
    
    public function addRecord($data)
    {
        $orderdata  = $this->create();
        $orderdata->setData($data);
        $orderdata->save();
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
    
    
    public function getPreOrderItems($_orderId)
    {
        $pre_order_item = [];
        $collection = $this->create()->getCollection()->addFieldToFilter('order_id', $_orderId);
        $pre_order_item = $collection->getColumnValues('sku');
        return $pre_order_item;
    }
            
    public function updateOrderPoRelationColumn($column, $value, $condition)
    {
        
        if (is_array($condition) and count($condition) > 0) {
            $collection = $this->create()->getCollection();
            foreach ($condition as $key => $item) {
                $collection->addFieldToFilter($key, $item);
            }
            if ($collection->getSize()) {
                $opr =  $collection->getFirstItem();
                if ($column and $opr) {
                    $opr->setData($column, $value);
                    $opr->save();
                }
            }
        }
        return true;
    }
    

    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function processOrderPoRelations()
    {
        $isPushThirdParty  =  $this->getConfig('bpconfiguration/thirdpartyapp/pushorderporelation');
        if ($isPushThirdParty) {
            $this->updateStuckQueueRecord();  // ------- update stuck queue records
            if ($this->checkQueueProcessingStatus()) { // ------- check previous cron running or not
                return true;
            }
            $collection =   $this->create()->getCollection();
            $collection->addFieldToFilter('state', ['eq'=>$this->pendingState]);
            $collection->addFieldToFilter('bp_order_id', ['gt'=> 0 ]);
            if (count($collection) > 0) {
                // --------  update state in processing state  --------------
                foreach ($collection as $item) {
                    $item->setState($this->processingState)->save();
                }
                $postData    = [];
                foreach ($collection as $item) {
                    $postData['orderporelation'][$item->getOrderId()]['mgt_order_id']         = $item->getOrderId();
                    $postData['orderporelation'][$item->getOrderId()]['bp_order_id']         = $item->getBpOrderId();
                    $postData['orderporelation'][$item->getOrderId()]['Items'][$item->getSku()]['po_id']     = $item->getPoId();
                    $postData['orderporelation'][$item->getOrderId()]['Items'][$item->getSku()]['sku']         = $item->getSku();
                    $postData['orderporelation'][$item->getOrderId()]['Items'][$item->getSku()]['qty']        = $item->getQty();
                    $updateDataArray     = [];
                    $updateDataArray ['state'] = $this->completeState ;
                    $this->updateRecord($item->getId(), $updateDataArray);
                }
                if (count($postData['orderporelation']) > 0) {
                    $jsonObject = json_encode($postData, true);
                    $this->send($jsonObject);
                }
            }
        }
        return true;
    }
                
    public function checkQueueProcessingStatus()
    {
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('state', [ 'eq'=>$this->processingState ]);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }

    public function updateStuckQueueRecord()
    {
        $adminHours = $this->getConfig('bpconfiguration/thirdpartyapp/max_hours_in_queue');
        if (!$adminHours) {
            $adminHours = 2;
        }
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('state', ['eq'=>$this->processingState]);
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

    protected function send($data)
    {
        $apiUrl = $this->getConfig('bpconfiguration/thirdpartyapp/app_url');
        if ($apiUrl) {
            $process = curl_init($apiUrl);
            curl_setopt($process, CURLOPT_HEADER, 1);
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, $data);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($process);
            curl_close($process);
            $this->recordLog("Push Order PO Relation to Thirdpasrty", $data, "Post Data");
            $this->recordLog("Push Order PO Relation to Thirdpasrty", $response, "Response");
        }
    }
    
    public function recordLog($cat, $log_data, $title)
    {
        $logArray = [];
        if (!$cat) {
            $cat = "Global";
        }
         $logArray['category'] = $cat;
         $logArray['title'] =  $title;
         $logArray['store_id'] =  0;
         $logArray['error'] =  json_encode($log_data, true);
         $this->_logManager->addLog($logArray);
         return true;
    }
}
