<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\nominals;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Synnominalsapi extends \Magento\Backend\App\Action
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
         $collection = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Nominals\Collection');
        $collection->walk('delete');
                 
     
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->getAllNominals();
            if (count($result)>0) {
                $headerColumn = [];
                $accountTypeArray = [];

                foreach ($result['response']['metaData']['columns'] as $item) {
                    $headerColumn[] = $item['name'];
                }
                foreach ($result['reference']['accountTypeName'] as $key => $value) {
                    $accountTypeArray[$key] = $value[0];
                }
                
                foreach ($result['response']['results'] as $item) {
                    $tmp = [];
                    $data = [];

                    $tmp = array_combine($headerColumn, $item);
                    
                    $data['name']                 = $tmp['name'];
                    $data['code']                 = $tmp['code'];
                    $data['account_type']         = $tmp['type'];
                    $account_type_id              = $tmp['type'];
                    $data['account_type_name']    = $accountTypeArray[$account_type_id];
                    $data['active']             = $tmp['active'];
                    $data['json']                 = json_encode($tmp, true);
                    
                      $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Nominals');
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Brightpearl nominals has been saved.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
