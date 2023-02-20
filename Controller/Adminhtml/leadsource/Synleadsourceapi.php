<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\leadsource;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Synleadsourceapi extends \Magento\Backend\App\Action
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
        $collection = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Leadsource\Collection');
        $collection->walk('delete');
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->getAllLeadSource();
            if (count($result)>0) {
                foreach ($result['response'] as $item) {
                    $data = [];
                    $data['bp_id'] = $item['id'];
                    $data['owner_id'] = $item['ownerId'];
                    $data['parent_id'] = $item['parentId'];
                    $data['name'] = $item['name'];
                    $data['is_active'] = $item['isActive'];
                    $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Leadsource');
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Brightpearl Lead Source has been saved.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
