<?php

namespace Bsitc\Brightpearl\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CustomerRegisterSuccess implements ObserverInterface
{
    protected $_objectManager;
    protected $_log;
    protected $_bpCustomerQueue;
	
	protected $_segmetricHelper;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\LogsFactory $logsFactory,
        \Bsitc\Brightpearl\Model\BpcustomerqueueFactory $bpCustomerQueue,
		\Bsitc\Brightpearl\Helper\SegmetricHelper $segmetricHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_log = $logsFactory;
        $this->_bpCustomerQueue = $bpCustomerQueue;
		$this->_segmetricHelper = $segmetricHelper;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $bpCustomerQueue = $this->_objectManager->create('\Bsitc\Brightpearl\Model\BpcustomerqueueFactory');
        $data = [];
        $data['customer_id'] = $customer->getId();
        $data['email'] = $customer->getEmail();
        $data['status'] = '1';
		
		$this->_segmetricHelper->postCustomerToSegmetric($customer->getId());
       // $bpCustomerQueue->addRecord($data);
        return $this;
    }
}
