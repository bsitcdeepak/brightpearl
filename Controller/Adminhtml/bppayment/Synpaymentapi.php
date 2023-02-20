<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\bppayment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Synpaymentapi extends \Magento\Backend\App\Action
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
        $collection = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Bppayment\Collection');
        $collection->walk('delete');
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->getAllPaymentMethod();
            if (count($result)>0) {
                foreach ($result['response'] as $item) {
                    $data = [];
                    $data['payment_id'] = $item['id'];
                    $data['name'] = $item['name'];
                    $data['code'] = $item['code'];
                    $data['isactive'] = $item['isActive'];
                    $data['bank_accounts'] = json_encode($item['bankAccounts']);
                    $data['installed_integration_id'] = $item['installedIntegrationId'];
                    $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bppayment');
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Brightpearl Payment Method has been saved.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
