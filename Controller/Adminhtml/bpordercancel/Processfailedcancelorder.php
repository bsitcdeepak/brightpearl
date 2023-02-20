<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\bpordercancel;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Processfailedcancelorder extends \Magento\Backend\App\Action
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
          $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\BpordercancelFactory');
        $model->processFailedCancelOrder();
         $this->messageManager->addSuccess(__('Process Failed Cancel Order has been successfully done.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
