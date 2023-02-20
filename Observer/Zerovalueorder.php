<?php

namespace Bsitc\Brightpearl\Observer;

class Zerovalueorder implements \Magento\Framework\Event\ObserverInterface
{

    protected $_objectManager;
    protected $_storemanager;
    protected $_orderqueuefactory;
    protected $_helperdata;
    
 
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bsitc\Brightpearl\Model\OrderqueueFactory $orderqueuefactory,
        \Bsitc\Brightpearl\Model\SalesorderreportFactory $salesorderreportFactory,
        \Bsitc\Brightpearl\Helper\Data $helperdata
    ) {
        $this->_objectManager              = $objectManager;
        $this->_storemanager               = $storeManager;
        $this->_orderqueuefactory          = $orderqueuefactory;
        $this->_salesorderreportFactory    = $salesorderreportFactory;
        $this->_helperdata                 = $helperdata;
    }
    
    public function alreadyExits($orderid)
    {
        $collection = $this->_orderqueuefactory->create()->getCollection()->addFieldToFilter('order_id', $orderid);
        if (count($collection)>0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function alreadySend($order)
    {
        $collection = $this->_salesorderreportFactory->create()->getCollection()->addFieldToFilter('mgt_order_id', $order->getIncrementId());
        if (count($collection)>0) {
            return true;
        } else {
            return false;
        }
    }
     
    public function skipOldOrder($order)
    {
        $created_at = $order->getCreatedAt();
        $configureDate = $this->_helperdata->getConfig('bpconfiguration/bp_orderconfig/skiporderfrom');
        if ($configureDate) {
            $configDateTimestamp     = strtotime($configureDate);
            $orderDateTimestamp     = strtotime($created_at);
            if ($orderDateTimestamp > $configDateTimestamp) {
                return false;
            } else {
                $log = $this->_objectManager->create('\Bsitc\Brightpearl\Model\LogsFactory');
                $msg = "Skip Old Order = Configure Date : ".$configureDate.'  Order Create At : '.$created_at;
                $log->recordLog($msg, $order->getIncrementId(), "Order");
                return true;
            }
        } else {
            $log = $this->_objectManager->create('\Bsitc\Brightpearl\Model\LogsFactory');
            $msg = "Please set the order post starting date in configuration";
            $log->recordLog($msg, $order->getIncrementId(), "Order");
            return false;
        }
    }
    

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helperdata->getBrightpearlEnable()) 
		{
            if ($this->_helperdata->getOrderqueueEnable()) 
			{
                $order = $observer->getEvent()->getOrder();
				if($order->getGrandTotal() == 0 )
				{
					$orderid         = $order->getId();
					$incrementid     = $order->getIncrementId();
					if ($this->skipOldOrder($order)) { return $this; } /* ---- skip Old Orders based on configure date */
					if ($this->alreadySend($order)) { return $this; } /* ---- Check if order already sent and exist in sent order report ---- */
					if ($this->alreadyExits($orderid)) { return $this; } /*---- Check if order already exits in Queue ------*/
					$data                     = [];
					$data['order_id']         =  $orderid;
					$data['increment_id']     =  $incrementid;
					$data['state']             =  $this->_orderqueuefactory->pendingState;
					$this->_orderqueuefactory->addRecord($data); /*  ---- add order in queue ------*/
					/* ------  Check order item for pre order item and add it order po relation --------*/
					/*  ItemTypeInfo :  1 => Pre Order , 2 => Made To Order , 3 => Print To Order , 4 => Bespok Order , 5 => Trade Order  */
					$orderedItems = $order->getAllItems();
					foreach ($orderedItems as $item) {
						if ($item->getItemTypeInfo() == '1') {
							$this->addOrderPoRelation($item, $order);
						}
					}
					return $this;
				}
            }
        }
    }

    public function addOrderPoRelation($orderItem, $order)
    {
		$poObj = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bppurchaseorders');
		$oprObj = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bporderporelation');
		$availability = $orderItem->getExpDeliveryDate();
        if ($availability != '') {
            $date = date_create($availability);
            $availability_dispDate = date_format($date, 'l jS F Y');
            $availability_date = date_format($date, 'm/d/y');
        }
		$result = $poObj->findPoBySkuPoid($orderItem->getPoId(), $orderItem->getSku());
		$poId = 0 ;
        if ($result) {
            $poId = $result->getPoId() ;
            $finalQty = $result->getQuantity() - $orderItem->getQtyOrdered();
            $result->setQuantity($finalQty);
            $result->save();
        }
		$opr = [];
		$opr['order_id'] =  $order->getId();
		$opr['po_id'] =  $poId;
		$opr['sku'] =  $orderItem->getSku();
		$opr['qty'] =  $orderItem->getQtyOrdered();
		$opr['orgdeliverydate'] =  $availability_date ;
		$opr['deliverydate'] =  $availability_date ;
		$opr['bp_order_id'] =  '' ;
		$opr['created_at'] =  $order->getCreateAt() ;
		$oprObj->addRecord($opr) ;
    }

}
