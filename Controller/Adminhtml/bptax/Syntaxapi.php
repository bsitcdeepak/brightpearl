<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\bptax;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Syntaxapi extends \Magento\Backend\App\Action
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
        $collection = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Bptax\Collection');
        $collection->walk('delete');
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->getAllTax();
            if (count($result)>0) {
                foreach ($result['response'] as $item) {
                    $data = [];
                    $data['bp_id'] = $item['id'];
                    $data['code'] = $item['code'];
                    $data['description'] = $item['description'];
                    if (array_key_exists('rate', $item)) {
                        $data['rate'] = $item['rate'];
                    }
                    $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bptax');
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Brightpearl tax status has been saved.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
