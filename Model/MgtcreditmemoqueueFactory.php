<?php

namespace Bsitc\Brightpearl\Model;

class MgtcreditmemoqueueFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    public $_logManager;
    public $_queuestatus;
    public $_mgtOrder;
    public $_helperdata;
    public $_date;
    public $_creditMemo;
 
    public $pendingState;
    public $processingState;
    public $completeState;
    public $errorState;
    public $goReleaseState;
    public $goProcessingState;
    public $itReceivedState;
    public $goErrorState;
    
    public $manualState;
    public $ignoreState;
    
    public $searchCriteriaBuilder;
    public $_resultstatus;
	
	public $segmetricHelper;
    
  
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\Mgtorder $mgtOrder,
        \Bsitc\Brightpearl\Helper\Data $helperdata,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditMemoRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Bsitc\Brightpearl\Model\Resultstatus $resultstatus,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
		\Bsitc\Brightpearl\Helper\SegmetricHelper $segmetricHelper
    ) {
        $this->_date                 = $date;
        $this->_objectManager         = $objectManager;
        $this->_logManager          = $logManager;
        $this->_queuestatus         = $queuestatus;
        $this->_mgtOrder             = $mgtOrder;
        $this->_helperdata             = $helperdata;
        $this->_creditMemo             = $creditMemoRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_resultstatus         = $resultstatus;
        
         $queue_status                = $this->_queuestatus->getQueueOptionArray();
        $this->pendingState            = $queue_status['Pending'];
        $this->processingState        = $queue_status['Processing'];
        $this->completeState        = $queue_status['Completed'];
        $this->errorState            = $queue_status['Error'];
        $this->goReleaseState        = $queue_status['GO_Release'];
        $this->goProcessingState    = $queue_status['IT_Received'];
        $this->itReceivedState        = $queue_status['GO_Processing'];
        $this->goErrorState            = $queue_status['GO_Error'];
        
        $result_status                 = $this->_resultstatus->getResultOptionArray();
        $this->manualState            = $result_status['Manually Settled'];
        $this->ignoreState            = $result_status['Ignore'];
		
		$this->segmetricHelper = $segmetricHelper;
    }
 
    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Mgtcreditmemoqueue', $arguments, false);
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
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $this->cleanProcesQueue(); // ----- remove complete record from queue
        
         $this->updateStuckQueueRecord();  // ------- update stuck queue records
        if ($this->checkQueueProcessingStatus()) {  // ------- check previous cron running or not
            return true;
        }
        $collection =   $this->create()->getCollection();
        $collection->addFieldToFilter('status', ['eq'=>$this->pendingState]);
        $tmpCollection = $collection;
        
        // -------------- filter good out note here ---------------
        if ($collection->getSize()) {
            /* --------  update status in processing state  ---------------*/
            foreach ($tmpCollection as $item) {
                $item->setStatus($this->processingState)->save();
            }
            
            foreach ($collection as $item) {
                $returnstockJson = [];
                $creditmemoId = $item->getCmId();
                if ($item->getJson()) {
                    $returnstockJson = json_decode($item->getJson(), true);
                }
                 $creditmemo = $this->_creditMemo->get($creditmemoId);
                if ($creditmemo->getId()) {
                    $response = $this->_mgtOrder->postCreditMemoToBP($creditmemo, $returnstockJson);
                    if ($response) {
                        $item->setStatus($this->completeState)->save();
                    } else {
                        $item->setStatus($this->errorState)->save();
                    }
					
					$this->segmetricHelper->postOrderToSegmetric($item->getOrderId());
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
         //  $collection->addFieldToFilter('status', array('eq'=>$this->processingState));
          $collection->addFieldToFilter('status', ['in' => [$this->processingState,$this->errorState]]);
          
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $date_a = $this->_date->date($item->getUpdatedAt());
                $date_b = $this->_date->date();
                $diff = $date_a->diff($date_b)->format('%i');
                if ($diff >= $adminHours) {
                    $item->setStatus($this->pendingState)->save();
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
    
    
    public function cleanProcesQueue()
    {
        
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', ['in' => [$this->completeState]]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $item->delete();
            }
        }
    }
    
    public function processFailedCreditmemo()
    {
 
         $api     = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
         $cmf     = $this->_objectManager->create('\Bsitc\Brightpearl\Model\MgtcreditmemoFactory');
         
        $d                 = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $finalDate         = date('Y-m-d H:i:s', strtotime($d->gmtDate() . ' - 5 minute'));
         
        $collection = $cmf->create()->getCollection();
        $collection->addFieldToFilter('sc_order_id', ['eq' => null]);
         $collection->addFieldToFilter('status', ['eq' => 0 ]);
        $collection->addFieldToFilter('updated_at', ['lteq' => $finalDate]);
        
        if (count($collection)>0) {
            foreach ($collection as $item) {
                  $serachResults     =   $api->serchSalesCreditByExternalRef($item->getMgtCreditmemoId()); /* --  search salescredit on BP */
                if ($serachResults) {
                    // -------- already created ---------
                    $bpscid  = $serachResults['orderId'];
                    $this->_logManager->recordLog('Creditmemo Manually Created in Brightpearl #'.$bpscid, "Mgt Order Id ".$item->getMgtOrderId(), "Creditmemo Retry");
                    $updateDataArray = [];
                    $updateDataArray ['so_order_id']     = $serachResults['parentOrderId'];
                    $updateDataArray ['sc_order_id']     = $serachResults['orderId'];
                    $updateDataArray ['status']         = $this->manualState ;
                    $updateDataArray ['updated_at']     = date('Y-m-d H:i:s');
                    $cmf->updateRecord($item->getId(), $updateDataArray);
                    /* ----- search in queue and remove it ----- */
                    $foundInQueue = $this->findRecord('cm_increment_id', $item->getMgtCreditmemoId());
                    if ($foundInQueue) {
                        $this->removeRecord($foundInQueue->getId());
                    }
                } else {
                    $mgtCreditmemo = $this->getCreditmemoByIncrementId($item->getMgtCreditmemoId());
                    if ($mgtCreditmemo) {
                        $row = [];
                        $row['cm_id']                 = $mgtCreditmemo->getId();
                        $row['cm_increment_id']     = $item->getMgtCreditmemoId();
                        $row['order_id']             = $mgtCreditmemo->getOrderId();
                        $row['order_increment_id']  = $item->getMgtOrderId();
                        $row['json']                 = $item->getJson();
                        $row['status']                 = $this->pendingState;
                        /* ----- search in queue ----- */
                        $foundInQueue = $this->findRecord('cm_increment_id', $item->getMgtCreditmemoId());
                        if ($foundInQueue) {
                            $this->removeRecord($foundInQueue->getId());
                        }
                        $this->addRecord($row);
                        $cmf->removeRecord($item->getId());
                        $this->_logManager->recordLog('Creditmemo added again in queue #'.$item->getMgtCreditmemoId(), "Mgt Order Id ".$item->getMgtOrderId(), "Creditmemo Retry");
                    }
                }
            }
        }
    }
    
    
    public function getCreditmemoByIncrementId($incrementId)
    {
        $mgtCreditmemo = '';
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();
        $creditmemo = $this->_creditMemo->getList($searchCriteria);
        $creditmemoId = null;
        if ($creditmemo->getTotalCount()) {
            foreach ($creditmemo->getItems() as $creditmemoData) {
                $mgtCreditmemo = $creditmemoData;
                break;
            }
        }
        return $mgtCreditmemo;
    }
}
