<?php

namespace Bsitc\Brightpearl\Model;

class BpordercancelFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_api;
    
    protected $_datahelper;

    protected $_salesOrderReport;
	
	public $segmetricHelper;

    
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Helper\Data $datahelper,
        \Bsitc\Brightpearl\Model\Salesorderreport $salesOrderReport,
		\Bsitc\Brightpearl\Helper\SegmetricHelper $segmetricHelper
    ) {
        $this->_objectManager             = $objectManager;
        $this->_api                        = $api;
        $this->_datahelper                = $datahelper;
        $this->_salesOrderReport        = $salesOrderReport;
		
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpordercancel', $arguments, false);
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
    
    public function removeRecord($id)
    {
        $record = $this->create()->load($id);
        if ($record) {
            $record->delete();
        }
        return true;
    }
    
    public function postCancelOrder($order)
    {

        $logObj     = $this->_objectManager->create('Bsitc\Brightpearl\Model\LogsFactory');
        $logObj->recordLog($order->getIncrementId(), "Start", "Cancel Order Event");

        if ($order) {
            $orderstatus     = $this->_datahelper->getCancelledStatus();
            $data             = $this->checkAllReadyExits($order->getId());
            if ($data == true) {
                $logObj->recordLog($order->getIncrementId(), "Already posted to Brightpearl", "Cancel Order Event");
                return;
            }
             $sorResults = $this->_salesOrderReport->findRecord('mgt_order_id', $order->getIncrementId());
            $bporderid = 0;
            foreach ($sorResults as $sorResult) {
                    $bporderid = $sorResult['bp_order_id'];
                    break;
            }

            $orderStatusArray = [];
            $data = [];
            $data['mgt_order_id']       = $order->getId();
            $data['mgt_increment_id'] = $order->getIncrementId();
            $data['bp_order_id']       = $bporderid;
            $data['status']           = 'failed';

            if ($data and $bporderid > 0) {
                if ($this->_api->authorisationToken) {
                    $orderStatusArray['orderStatusId'] = $orderstatus;
                    if ($bporderid) {
                        $result = $this->_api->updateOrderStatus($bporderid, $orderStatusArray);
                        $logObj->recordLog(json_encode($result, true), "bp updateOrderStatus response ", "Cancel Order Event");
                        $data['status'] = 'success';
                    }
                }
				
				$this->segmetricHelper->postCancelOrder($order->getId());
				
            } else {
                 $logObj->recordLog($bporderid, "bp id or data not found", "Cancel Order Event");
            }

            $this->addRecord($data);
        }
    }
        
    public function checkAllReadyExits($orderid)
    {
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('mgt_order_id', $orderid);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }

    public function processFailedCancelOrder()
    {
        $d = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $finalDate = date('Y-m-d H:i:s', strtotime($d->gmtDate() . ' - 60 minute'));
        
        $orderFactory = $this->_objectManager->create('\Magento\Sales\Api\Data\OrderInterface') ;

        $collections = $this->create()->getCollection();
        $collections->addFieldToFilter('status', ['eq'=>'failed']);
        $collections->addFieldToFilter('created_at', ['lteq' => $finalDate]);
        if (count($collections) > 0) {
            foreach ($collections as $collection) {
                $orderIncrementId = $collection->getMgtIncrementId();
                $order = $orderFactory->loadByIncrementId($orderIncrementId);
                 $this->removeRecord($collection->getId());
                $this->postCancelOrder($order);
            }
        }
    }
}
