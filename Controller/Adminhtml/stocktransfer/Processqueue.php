<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\stocktransfer;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Processqueue extends \Magento\Backend\App\Action
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
          $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\StocktransferFactory');
        $model->processQueue();
         $this->messageManager->addSuccess(__('Queue has been successfully process.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
