<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\webhooks;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
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
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Webhooks');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            } else {
                /* ------------ create webhook fired only new mode not edit mode ------------- */
                $wh_data =     '{
					"subscribeTo": "'.trim($data['subscribe_to']).'",
					"httpMethod": "POST",
					"uriTemplate": "'.trim($data['uri_template']).'",
					"bodyTemplate": "{ \"accountCode\": \"${account-code}\", \"resourceType\": \"${resource-type}\", \"id\": \"${resource-id}\", \"lifecycleEvent\": \"${lifecycle-event}\", \"fullEvent\": \"${full-event}\", \"raisedOn\": \"${raised-on}\", \"brightpearlVersion\": \"${brightpearl-version}\" }",
					"contentType": "application/json",
					"idSetAccepted": false,
					"qualityOfService": 0
					}';
                     
                      $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
                if ($api->authorisationToken) {
                     $result = $api->createWebhook($wh_data);
                    if (array_key_exists('response', $result)) {
                          $model->setWebhookId($result['response']);
                          $data['webhook_id'] = $result['response'];
                    }
                }
            }
            
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Webhooks has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Webhooks.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
