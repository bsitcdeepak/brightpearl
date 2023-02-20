<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\webhookupdate;

class Deleteall extends \Magento\Backend\App\Action
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\WebhookupdateFactory');
        $model->cleanall();
         $this->messageManager->addSuccess(__('Clean queue successfully.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
