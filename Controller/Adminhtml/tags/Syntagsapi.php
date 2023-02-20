<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\tags;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Syntagsapi extends \Magento\Backend\App\Action
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
        $collection = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Tags\Collection');
        $collection->walk('delete');
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->getAllTag();
            if (count($result)>0) {
                foreach ($result['response'] as $item) {
                    $data = [];
                     $data['tag_id'] = $item['tagId'];
                    $data['name'] = $item['tagName'];
                    $data['json'] = json_encode($item, true);
                     $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Tags');
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Brightpearl tag has been saved.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
