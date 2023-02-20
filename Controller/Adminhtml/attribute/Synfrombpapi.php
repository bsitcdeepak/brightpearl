<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\attribute;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Synfrombpapi extends \Magento\Backend\App\Action
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
          $model = $this->_objectManager->create('\Bsitc\Brightpearl\Model\AttributeFactory');
        $model->setAttributeOptions();
         $this->messageManager->addSuccess(__('Queue has been successfully process.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
