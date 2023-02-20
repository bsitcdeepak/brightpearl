<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\dmsubscriber;

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
        $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\DmsubscriberFactory');

        // 2. Initial checking 
        if ($id) {
            $record = $model->create()->load($id);
            if ($record->getId() and ( $record->getStatus() == '4' || $record->getStatus() == '6'  )) 
			{
				$model->postCustomerDotDigitalTradeProgram($record);
				$this->messageManager->addSuccess(__('Resend Order successfully.'));
            }else{
				$this->messageManager->addSuccess(__('Unable to process your request.'));
			}
        }
         
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
