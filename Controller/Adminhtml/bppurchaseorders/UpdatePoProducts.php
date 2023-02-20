<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\bppurchaseorders;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class UpdatePoProducts extends \Magento\Backend\App\Action
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
        $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\BppurchaseordersFactory');
        $model->cronUpdatePreOrderProductStatus();
        $this->messageManager->addSuccess(__('Pre Order Product status update successfully.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
