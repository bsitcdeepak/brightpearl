<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\bpproductimage;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Synupdatedimages extends \Magento\Backend\App\Action
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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\BpproductimageFactory');
        $model->SyncMgtProUpdate();
         $this->messageManager->addSuccess(__('Queue has been successfully process.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
