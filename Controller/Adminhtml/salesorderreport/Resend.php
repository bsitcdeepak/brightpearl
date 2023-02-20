<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\salesorderreport;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Resend extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Salesorderreport');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if ($model->getId() and $model->getBpOrderId() == '') {
                 $orderIncrementId = $model->getMgtOrderId();
                  $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->loadByIncrementId($orderIncrementId);
                if ($order->getId()) {
                    $_salesOrderHelper = $this->_objectManager->create('Bsitc\Brightpearl\Helper\SalesOrder');
                     $_salesOrderHelper->CreateOrder($order->getId(), $order->getIncrementId());
                    //$model->delete();
                    $this->messageManager->addSuccess(__('Resend Order successfully.'));
                }
                 // Bsitc\Brightpearl\Helper\SalesOrder  100079
            }
        }
         
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
