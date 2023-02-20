<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;

class Clearrawdata extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
 
    public function __construct(
        Context $context,
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }
    
 
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (isset($data['tbl'])) {
                $resource     = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tbl = $resource->getTableName($data['tbl']);
                if (strpos($tbl, 'bsitc') !== false) {
                    // echo '<pre>'; print_r( $tbl ); echo '</pre>';
                    if ($connection->isTableExists($tbl)) {
                        $connection->truncateTable($tbl);
                        $this->messageManager->addSuccess(__('operation  successfully completed.'));
                    } else {
                        $this->messageManager->addSuccess(__('operation  failed.'));
                    }
                } else {
                    $this->messageManager->addSuccess(__('unable to complete your request.'));
                }
            }
        }
        return $resultRedirect->setPath('*/index/');
    }
}
