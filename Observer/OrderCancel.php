<?php

namespace Bsitc\Brightpearl\Observer;

class OrderCancel implements \Magento\Framework\Event\ObserverInterface
{
    protected $_objectManager;
    protected $_log;
    protected $_helperdata;
    protected $_ordercancel;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\LogsFactory $logsFactory,
        \Bsitc\Brightpearl\Model\BpordercancelFactory $orderCancel,
        \Bsitc\Brightpearl\Helper\Data $helperdata
    ) {
        $this->_objectManager    = $objectManager;
        $this->_log             = $logsFactory;
        $this->_helperdata         = $helperdata;
        $this->_ordercancel     = $orderCancel;
    }

    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $data = $this->_ordercancel->postCancelOrder($order);
        return $this;
    }
}
