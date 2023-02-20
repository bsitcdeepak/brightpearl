<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\logs;

class Clean extends \Magento\Backend\App\Action
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\LogsFactory');
        $model->clean();
         $this->messageManager->addSuccess(__('Log has been clean successfully.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
