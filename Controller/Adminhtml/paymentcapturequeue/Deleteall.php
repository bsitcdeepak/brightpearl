<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\paymentcapturequeue;

class Deleteall extends \Magento\Backend\App\Action
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\PaymentcapturequeueFactory');
        $model->removeAllRecord();
         $this->messageManager->addSuccess(__('Queue clean successfully.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
