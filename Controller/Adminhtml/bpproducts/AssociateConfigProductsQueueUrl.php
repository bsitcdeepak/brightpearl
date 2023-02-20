<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\bpproducts;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class AssociateConfigProductsQueueUrl extends \Magento\Backend\App\Action
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
        
        $model = $this->_objectManager->create('\Bsitc\Brightpearl\Helper\Data');
        $model->setAssociateProducts();

         $this->messageManager->addSuccess(__('Queue has been successfully process.'));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
