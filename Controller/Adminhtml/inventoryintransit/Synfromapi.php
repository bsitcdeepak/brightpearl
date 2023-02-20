<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\inventoryintransit;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Synfromapi extends \Magento\Backend\App\Action
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

         $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Inventoryintransit');
        $model->syncFromApi();
         $this->messageManager->addSuccess(__('Fetch Inventory transfers report'));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
