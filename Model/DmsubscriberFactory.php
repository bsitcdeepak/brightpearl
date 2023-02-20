<?php

namespace Bsitc\Brightpearl\Model;

class DmsubscriberFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $_date;
    public $_objectManager;
    public $_storeManager;
    public $_scopeConfig;
    public $_queuestatus;

    public $pendingState;
    public $processingState;
    public $completeState;
    public $errorState;
    public $errorFailed;
	
    public $_dotmalier;
    public $_data;


    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Bsitc\Brightpearl\Model\Dotmalier $dotmalier
	)
    {
        $this->_date				= $date;
        $this->_objectManager		= $objectManager;
        $this->_storeManager		= $storeManager;
        $this->_queuestatus			= $queuestatus;
        $this->_customerFactory		= $customerFactory;
        $this->_dotmalier			= $dotmalier;
        
        $queue_status				= $this->_queuestatus->getQueueOptionArray();
        $this->pendingState			= $queue_status['Pending'];
        $this->processingState		= $queue_status['Processing'];
        $this->completeState		= $queue_status['Completed'];
        $this->errorState			= $queue_status['Error'];
        $this->errorFailed			= $queue_status['Failed'];
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Dmsubscriber', $arguments, false);
    }
	
    public function addRecord($row, $returnId = '')
    {
        if (count($row)>0) {
            $record = $this->create();
            $record->setData($row);
            $record->save();
			if($returnId){
				return $record->getId();
			}
        }
        return true;
    }
    
    public function updateRecord($id, $row)
    {
        $record =  $this->create()->load($id);
        $record->setData($row);
        $record->setId($id);
        $record->save();
    }
    
    public function findRecord($column, $value)
    {
        $data = '';
        $collection = $this->create()->getCollection()->addFieldToFilter($column, $value);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
        }
        return $data;
    }

    public function removeAllRecord()
    {
        $collection = $this->create()->getCollection();
        $collection->walk('delete');
        return true;
    }
    
    public function removeRecord($id)
    {
        $record = $this->create()->load($id);
        if ($record) {
            $record->delete();
        }
        return true;
    }
	
	public function addCustomerInDotDigitalTradeProgram($customer)
	{
		if($this->_dotmalier->enable and $this->_dotmalier->programId != "" )
		{
			$email = $customer->getEmail();
			$data = array();
			$data['customer_id'] 	= $customer->getId();
			$data['fname'] 			= $customer->getFirstname();
			$data['lname'] 			= $customer->getLastname();
			$data['email'] 			= $customer->getEmail();
			$data['status'] 		= $this->pendingState;
			$data['created_at'] 	= $this->_date->date();
			$insertId   			= $this->addRecord($data, 'id');
	 
			$result = $this->_dotmalier->postEnrolment($customer->getEmail());
			if (!array_key_exists("error",$result))
			{
				$updateArray = array();
				$updateArray['json'] = json_encode($result,true);
				$updateArray['status'] = $this->completeState;
				$this->updateRecord($insertId, $updateArray);
				
			}else{
				$updateArray = array();
				$updateArray['json'] = json_encode($result,true);
				$updateArray['status'] = $this->errorFailed;
				$this->updateRecord($insertId, $updateArray);
			}
		}else{
			 $this->_dotmalier->recordLog('DM configuration not enableor have blank data', 'DM API Error');
		}
 	}
	
	
	public function postCustomerDotDigitalTradeProgram($record)
	{
        if ($record) 
		{
			$customerId = $record->getCustomerId();
			$mgtCustomer = $this->_customerFactory->create()->load($customerId);
            $record->delete();
			// $this->addCustomerInDotDigitalTradeProgram($mgtCustomer);
			$this->addCustomerInDotDigitalAddressBook($mgtCustomer);
		}
		return true;		
	}
	
	public function addCustomerInDotDigitalAddressBook($customer)
	{
		if($this->_dotmalier->enable and $this->_dotmalier->addressBookId != "" )
		{
			$email = $customer->getEmail();
			$data = array();
			$data['customer_id'] 	= $customer->getId();
			$data['fname'] 			= $customer->getFirstname();
			$data['lname'] 			= $customer->getLastname();
			$data['email'] 			= $customer->getEmail();
			$data['status'] 		= $this->pendingState;
			$data['created_at'] 	= $this->_date->date();
			$insertId   			= $this->addRecord($data, 'id');
			
			$genderText = $customer->getResource()->getAttribute('gender')->getSource()->getOptionText($customer->getData('gender'));

			$postData = array();
			$postData['email'] = $customer->getEmail();
			$postData['optInType'] = 'Single';
			$postData['emailType'] = 'Html';
			$postData['dataFields'] = [ 
									['key'=>'FIRSTNAME', 'value'=>$customer->getFirstname() ], 
									['key'=>'LASTNAME', 'value'=>$customer->getLastname() ],
									['key'=>'FULLNAME', 'value'=>$customer->getFirstname().' '.$customer->getLastname() ],
									['key'=>'GENDER', 'value'=>$genderText ],
								  ];		
			
			$result = $this->_dotmalier->postContactInaddressBook($postData);
			if (!array_key_exists("error",$result))
			{
				$updateArray = array();
				$updateArray['json'] = json_encode($result,true);
				$updateArray['status'] = $this->completeState;
				$this->updateRecord($insertId, $updateArray);
				
			}else{
				$updateArray = array();
				$updateArray['json'] = json_encode($result,true);
				$updateArray['status'] = $this->errorFailed;
				$this->updateRecord($insertId, $updateArray);
			}
		}else{
			 $this->_dotmalier->recordLog('DM configuration not enableor have blank data', 'DM API Error');
		}
 	}
	
	
}