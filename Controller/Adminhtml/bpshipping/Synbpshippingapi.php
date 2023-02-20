<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\bpshipping;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Synbpshippingapi extends \Magento\Backend\App\Action
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
         $collection = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Bpshipping\Collection');
        $collection->walk('delete');
     
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->getAllBpShippingMethods();
            if (count($result)>0) {
                foreach ($result['response'] as $item) {
                    $data = [];
                    $data['bpid'] = $item['id'];
                    $data['bpcode'] = $item['code'];
                    $data['bpname'] = $item['name'];
                    $data['breaks'] = $item['breaks'];
                    $data['method_type'] = $item['methodType'];
                    $data['additional_information_required'] = $item['additionalInformationRequired'];
                    $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpshipping');
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Brightpearl Shipping Methods has been saved.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
