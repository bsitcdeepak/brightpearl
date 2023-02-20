<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Tporders extends \Magento\Framework\App\Action\Action
{
    protected $_thirdPartyOrdersFactory;
    protected $_log;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\ThirdpartyordersFactory $thirdPartyOrdersFactory,
        \Bsitc\Brightpearl\Model\LogsFactory $logsFactory
    ) {
        $this->_thirdPartyOrdersFactory    = $thirdPartyOrdersFactory;
        $this->_log                         = $logsFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = [];
        $whObj = file_get_contents('php://input');
        $data = json_decode($whObj, true);
        $this->_log->recordLog($whObj, "Third Party Orders", "Third Party Orders Json");
        
        $this->_thirdPartyOrdersFactory->saveThirdpartyorders($data);
        
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
