<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\bporderstatus;

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
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
         $collection = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Bporderstatus\Collection');
        $collection->walk('delete');
                 
     
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->getAllOrderStatus();
            if (count($result)>0) {
                foreach ($result['response'] as $item) {
                    $data = [];
                    $data['status_id'] = $item['statusId'];
                    $data['name'] = $item['name'];
                    $data['order_type_code'] = $item['orderTypeCode'];
                    $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bporderstatus');
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Brightpearl Order Status has been saved.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
