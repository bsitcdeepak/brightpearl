<?php

namespace Bsitc\Brightpearl\Observer;

class Creditmemo implements \Magento\Framework\Event\ObserverInterface
{
    protected $_objectManager;
    protected $_log;
    protected $_mgtOrder;
    protected $_helperdata;
    
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\LogsFactory $logsFactory,
        \Bsitc\Brightpearl\Model\Mgtorder $mgtOrder,
        \Bsitc\Brightpearl\Helper\Data $helperdata
    ) {
        $this->_objectManager                    = $objectManager;
        $this->_log                             = $logsFactory;
        $this->_mgtOrder                         = $mgtOrder;
        $this->_helperdata                         = $helperdata;
    }

    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* -------- Check if BP module are enable ------------ */
        if ($this->_helperdata->getBrightpearlEnable()) {
            $creditmemo = $observer->getData('creditmemo');
            $this->_mgtOrder->addCreditMemoInQueue($creditmemo);
        }
        return $this;
    }
}
