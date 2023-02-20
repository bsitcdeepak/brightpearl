<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
 
class Saveskubpid extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
 
    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $filesystem;
 
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader
     */
    protected $fileUploader;
 
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        UploaderFactory $fileUploader
    ) {
        $this->messageManager       = $messageManager;
        $this->filesystem           = $filesystem;
        $this->fileUploader         = $fileUploader;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        parent::__construct($context);
    }
    
 
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $file = $_FILES['skubpid'] ;
            if (!isset($file['tmp_name'])) {
                $this->messageManager->addError('Invalid file upload attempt.');
                return $resultRedirect->setPath('*/*/');
                // throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
            }
            $csv = $this->_objectManager->create('\Magento\Framework\File\Csv');
            $csvData = $csv->getData($file['tmp_name']);
            $all_rows = [];
            $header = null;
            foreach ($csvData as $key => $data) {
                if ($header === null) {
                    $header = $data;
                    continue;
                }
                $all_rows[] = array_combine($header, $data);
            }
            
            if (count($all_rows)>0) {
                $resource     = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $bsbpi = $resource->getTableName('bsitc_brightpearl_bpitems');
                $connection->truncateTable($bsbpi);
                $connection->insertMultiple($bsbpi, $all_rows);
                $this->messageManager->addSuccess(__('File has been successfully uploaded.'));
            }
        }
        return $resultRedirect->setPath('*/bpitems/');
    }
}
