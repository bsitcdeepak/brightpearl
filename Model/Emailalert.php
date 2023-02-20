<?php

namespace Bsitc\Brightpearl\Model;

class Emailalert extends \Magento\Framework\Model\AbstractModel
{
    
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_log;
    protected $_objectManager;
    protected $_storeManager;
    protected $_sorf;
    public $_date;
    public $_api;
    
    public $foa_enable;
    public $foa_toname;
    public $foa_toemail;
    public $ccemail;
    public $fromName;
    public $fromemail;
    
    public $fia_enable;
    public $fia_toname;
    public $fia_toemail;
         
    const XML_PATH_EMAIL_RECIPIENT_NAME = 'trans_email/ident_support/name';
    const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'trans_email/ident_support/email';
    
    const XML_PATH_EMAIL_RECIPIENT = 'test/email/send_email';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $objectManager            = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_objectManager    =  $objectManager;
    }
    
    public function initializeEmailObjects()
    {
          $this->_storeManager         = $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
         $this->_inlineTranslation     = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
        $this->_transportBuilder     = $this->_objectManager->create('Magento\Framework\Mail\Template\TransportBuilder');
        $this->_scopeConfig         = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
          $this->_sorf                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\SalesorderreportFactory');
         $this->_log                 = $this->_objectManager->create('Bsitc\Brightpearl\Model\LogsFactory');
         $this->_date                 = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
         $this->_api                    = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $emailalertConfig     =     $this->_scopeConfig ->getValue('bpconfiguration/emailalert', $storeScope);
         $emailalert            =   (array)$emailalertConfig;
        $this->foa_enable    =    isset($emailalert['foa_enable']) ? $emailalert['foa_enable'] : 0 ;
        $this->foa_toname    =    isset($emailalert['foa_toname']) ? $emailalert['foa_toname'] : 'Admin' ;
        $this->foa_toemail    =    isset($emailalert['foa_toemail']) ? $emailalert['foa_toemail'] : 'kai@businesssolutionsinthecloud.com' ;
        $this->ccemail        =    isset($emailalert['foa_ccemail']) ? $emailalert['foa_ccemail'] : '' ;
         $this->fromName        =    isset($emailalert['foa_sendername']) ? $emailalert['foa_sendername'] : $this->_scopeConfig ->getValue('trans_email/ident_general/name', $storeScope);
        $this->fromemail    =    isset($emailalert['foa_enderemail']) ? $emailalert['foa_enderemail'] : $this->_scopeConfig ->getValue('trans_email/ident_general/email', $storeScope);
        
        $this->fia_enable    =    isset($emailalert['fia_enable']) ? $emailalert['fia_enable'] : 0 ;
        $this->fia_toname    =    isset($emailalert['fia_toname']) ? $emailalert['fia_toname'] : 'Admin' ;
        $this->fia_toemail    =    isset($emailalert['fia_toemail']) ? $emailalert['fia_toemail'] : 'kai@businesssolutionsinthecloud.com' ;
    }

    public function failedOrderAlert()
    {
        $this->initializeEmailObjects();
        if ($this->foa_enable) {
            $finalDate = date('Y-m-d H:i:s', strtotime('-15 minute'));
            $sorfCollection = $this->_sorf->create()->getCollection();
            $sorfCollection->addFieldToFilter('bp_order_id', ['null' => true]);
            $sorfCollection->addFieldToFilter('update_at', ['lteq' => $finalDate]);
            // $sorfCollection->addFieldToFilter('update_at', ['gteq' => $finalDate]);
            $mgt_order_ids =  $sorfCollection->getColumnValues('mgt_order_id');
            if (is_array($mgt_order_ids) and count($mgt_order_ids) > 0) {
                $emailData = [];
                $emailData['subject']         = 'BP Order Post Failed Alert';
                $emailData['customer_name']    = $this->foa_toname;
                $emailData['toName']         = $this->foa_toname;
                $emailData['toemail']         = $this->foa_toemail;
                $emailData['msg1']             = 'Unfortunately We are unable to post following order on Brightpearl.';
                $emailData['msg2']             = 'Please check the failed reason in log report.';
                $emailData['msg3']             = 'Failed Order : '.implode(", ", $mgt_order_ids);
                $emailData['msg4']             = 'You can manually send these order from the send order grid in admin section.';
                $emailData['msg5']             = 'Sorry for the inconvenience.';
                 $this->sendMail($emailData);
            }
            
            $this->failedOrderRowAlert();
            
            return true;
        }
    }


    public function sendMail($data)
    {
        $toName        = $data['toName'];
        $toemail    = $data['toemail'];
        $sender     = [ 'name' =>  $this->fromName, 'email' => $this->fromemail ];
        try {
            $this->_inlineTranslation->suspend();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            if ($this->ccemail !="") {
                $transport = $this->_transportBuilder
                ->setTemplateIdentifier('alert_email_template')
                ->setTemplateOptions(['area' => 'frontend','store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,])
                ->setTemplateVars($data)
                ->setFrom($sender)
                ->addTo($toemail, $toName)
                ->addCc($this->ccemail)
                ->getTransport();
            } else {
                $transport = $this->_transportBuilder
                ->setTemplateIdentifier('alert_email_template')
                ->setTemplateOptions(['area' => 'frontend','store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,])
                ->setTemplateVars($data)
                ->setFrom($sender)
                ->addTo($toemail, $toName)
                ->getTransport();
            }
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
            //$this->messageManager->addSuccess('Email sent successfully');
            return true;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->_log->recordLog($errorMessage, "Email Alert", "Email Alert");
            return true;
        }
     
        return true;
    }


    public function stuckInventoryQueueAlert()
    {
        
        $this->initializeEmailObjects();
        
        if ($this->fia_enable) {
            $finalDate = date('Y-m-d H:i:s', strtotime('-120 minute'));
            $webhookinventory = $this->_objectManager->create('\Bsitc\Brightpearl\Model\WebhookinventoryFactory');
            $collection = $webhookinventory->create()->getCollection();
            $collection->addFieldToFilter('status', 'processing');
            $collection->addFieldToFilter('updated_at', ['lteq' => $finalDate]);
            $bp_ids =  $collection->getColumnValues('bp_id');
            if (is_array($bp_ids) and count($bp_ids) > 0) {
                $emailData = [];
                $emailData['subject']         = 'BP Inventory Updated Queue Stuck Alert';
                $emailData['customer_name']    = $this->fia_toname;
                $emailData['toName']         = $this->fia_toname;
                $emailData['toemail']         = $this->fia_toemail;
                $emailData['msg1']             = 'Unfortunately Inventory Update Queue Processing is Stuck.';
                $emailData['msg2']             = 'Please clear the queue.';
                $emailData['msg3']             = 'Stuck Row : '.implode(", ", $bp_ids);
                $emailData['msg4']             = 'You can manually process this queqe.';
                $emailData['msg5']             = 'Sorry for the inconvenience.';
                $this->sendMail($emailData);
            }
            return true;
        }
    }

    public function failedOrderRowAlert()
    {
            $this->initializeEmailObjects();
            $startDate     = date('Y-m-d H:i:s', strtotime('-15 minute'));
            $logCollection = $this->_log->create()->getCollection();
            $logCollection->addFieldToFilter('category', 'Order');
            $logCollection->addFieldToFilter('error', ['like' => '%You have sent too many requests%']); // Note the spaces
             $logCollection->addFieldToFilter('updated_at', ['gteq' => $startDate]);
        if ($logCollection->getSize()) {
            $incompleteOrder = [];
            foreach ($logCollection as $log) {
                $incompleteOrder[$log->getTitle()] =$log->getTitle();
            }
                
            $emailData = [];
            $emailData['subject']         = 'BP Post Incomplete Order Alert';
            $emailData['customer_name']    = $this->foa_toname;
            $emailData['toName']         = $this->foa_toname;
            $emailData['toemail']         = $this->foa_toemail;
            $emailData['msg1']             = 'Unfortunately, we are unable to post following order completely to Brightpearl.';
            $emailData['msg2']             = 'Please check the failed reason in log report.';
            $emailData['msg3']             = 'Incomplete Order : '.implode(", ", $incompleteOrder);
            $emailData['msg4']             = 'Sorry for the inconvenience.';
            $emailData['msg5']             = '';
             $this->sendMail($emailData);
        }
            return true;
    }
}
