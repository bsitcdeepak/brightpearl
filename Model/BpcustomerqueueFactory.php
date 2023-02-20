<?php

namespace Bsitc\Brightpearl\Model;

class BpcustomerqueueFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
     
    public $_date;
    public $_objectManager;
    public $_storeManager;
    public $_scopeConfig;
    public $_queuestatus;
    public $_bpcustomerreport;

    public $pendingState;
    public $processingState;
    public $completeState;
    public $errorState;
    public $errorFailed;
    public $errorNotPaid;

    public $_customerFactory;
    public $_addressFactory;
    public $_helperCustomer;
    
    public $_subscriber;
    public $_dmSubscriberFactory;
	
	public $segmetricHelper;  

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
	 
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Bsitc\Brightpearl\Model\BpcustomerreportFactory $bpCustomerReportfactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Bsitc\Brightpearl\Helper\CustomerHelper $helperCustomer,
        \Bsitc\Brightpearl\Model\DmsubscriberFactory $dmSubscriberFactory,		
        \Magento\Newsletter\Model\Subscriber $subscriber,
		\Bsitc\Brightpearl\Helper\SegmetricHelper $segmetricHelper
    ) {
        $this->_date                = $date;
        $this->_objectManager        = $objectManager;
        $this->_storeManager        = $storeManager;
        $this->_queuestatus            = $queuestatus;
        $this->_bpcustomerreport    = $bpCustomerReportfactory;
        
        
        $this->_customerFactory        = $customerFactory;
        $this->_addressFactory        = $addressFactory;
        $this->_helperCustomer        = $helperCustomer;
        $this->_subscriber            = $subscriber;
        $this->_dmSubscriberFactory   = $dmSubscriberFactory;		
        
        $queue_status                = $this->_queuestatus->getQueueOptionArray();
        $this->pendingState            = $queue_status['Pending'];
        $this->processingState        = $queue_status['Processing'];
        $this->completeState        = $queue_status['Completed'];
        $this->errorState            = $queue_status['Error'];
        $this->errorFailed            = $queue_status['Failed'];
        $this->errorNotPaid            = $queue_status['Not_Paid'];
		
		$this->segmetricHelper = $segmetricHelper;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpcustomerqueue', $arguments, false);
    }
    
    public function addRecord($data)
    {
        $orderdata  = $this->create();
        $orderdata->setData($data);
        $orderdata->save();
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
    
	public function addCustomerFromCompanyCollection()
	{
		$isTradeEnable =  $this->_helperCustomer->getConfig('bpconfiguration/tradecustomer/enable');
		if($isTradeEnable)
		{
			$collection = $this->_objectManager->create('\Magento\Company\Model\ResourceModel\Company\Collection'); 
			$companyCollection = $collection->joinAdvancedCustomerEntityTable()->joinCustomerTable();
			$companyCollection->addFieldToFilter('status','1');
			if ($companyCollection->getSize()) 
			{
				foreach($companyCollection as $sku => $company)
				{
					$data = $this->_bpcustomerreport->findRecord('customer_id', $company->getSuperUserId());
					if ($data) {
						continue;
					}else{
						$row = array();
						$row['customer_id'] = $company->getSuperUserId();
						$row['email'] = $company->getEmailAdmin();
						$row['status'] = $this->pendingState;
						$chkQue = $this->findRecord('customer_id', $company->getSuperUserId());
						if ($chkQue) {
							// $this->UpdateRecord($data->getId(), $row);
						}else{
							$this->addRecord($row);
						}
					}
				}
			}
		}
	}
	
	public function addCustomerFromCustomerCollection()
	{
		$isTradeEnable =  $this->_helperCustomer->getConfig('bpconfiguration/tradecustomer/enable');
		
		$customerpoststartdate =  $this->_helperCustomer->getConfig('bpconfiguration/tradecustomer/customerpoststartdate');
		
		if($isTradeEnable)
		{
			$groupids =  $this->_helperCustomer->getConfig('bpconfiguration/tradecustomer/groupid');
			if($groupids == ""){
				return true;
			}
 			
			$bpPostedCustomers = array();
			$bpCustomerReportCollection = $this->_bpcustomerreport->create()->getCollection();
			$bpPostedCustomers = $bpCustomerReportCollection->getColumnValues('email');

			$customerFactory = $this->_objectManager->create('\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory');	
			$collection = $customerFactory->create();  
 			$collection->addFieldToFilter( 'group_id',array( 'in' =>  $groupids ) ); 	
			if( count($bpPostedCustomers) > 0 ){
				$collection->addFieldToFilter( 'email',array( 'nin' =>  $bpPostedCustomers ) ); 	
			}
			
 			if($customerpoststartdate and $customerpoststartdate !="")
			{
				$startDate = date("Y-m-d",strtotime($customerpoststartdate)); // start date
				$collection->addFieldToFilter('created_at', ['gteq' => $startDate]);
			}
			
			if ($collection->getSize()) 
			{
				$customerRepository = $this->_objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface');
				foreach($collection as  $customer)
				{
					$companyAttributes = $customerRepository->getById( $customer->getId() )->getExtensionAttributes()->getCompanyAttributes();
					if( $companyAttributes->getStatus() == '1')
					{
						$row = array();
						$row['customer_id'] = $customer->getId();
						$row['email'] = $customer->getEmail();
						$row['status'] = $this->pendingState;
						$chkQue = $this->findRecord('customer_id', $customer->getId());
						if ($chkQue) {
							// $this->UpdateRecord($data->getId(), $row);
						}else{
							$this->addRecord($row);
						}
					}
				}				
			}
		}
	}
	
    public function processQueue()
    {
		
		/* $collection =   $this->create()->getCollection();
		 foreach ($collection as $item) 
		 {
			  
			$customerId    = $item->getCustomerId();
			$this->segmetricHelper->postCustomerToSegmetric($customerId);
		 }
		 
		die('main');  */
		
		
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		
		$isDotmailerEnable =  $this->_helperCustomer->getConfig('bpconfiguration/dotmailer/enable');
		
		$this->addCustomerFromCustomerCollection(); // ----- active record in queue		
        
        $this->cleanProcesQueue(); // ----- remove complete and not paid satus record from queue
        $this->updateStuckQueueRecord();  // ------- update stuck queue records
        $this->removeNotConfigureGroupCustomerFromQueue();  // ---- remove customer if cusatomer group not match with configure group.
		
		
        
        if ($this->checkQueueProcessingStatus()) { // ------- check previous cron running or not
            return true;
        }
        $collection =   $this->create()->getCollection();
        $collection->addFieldToFilter('status', ['eq'=>$this->pendingState]);
        
        if (count($collection) > 0) {
            /* --------  update status =  processing status  ---------------*/
            foreach ($collection as $item) {
                $item->setState($this->processingState)->save();
            }
            /* --------  start processing  ---------------*/
            foreach ($collection as $item) {
                
                $customerInterface = $this->_objectManager->create('\Magento\Company\Model\CompanyManagement');
                $company = $customerInterface->getByCustomerId($item->getCustomerId());
                if ($company) {
                    if ($company->getStatus() != '1') {
                        //$item->setState($this->pendingState)->save();$record->delete();
                        $item->delete();
                        continue;
                    }
                }
				
                $updateDataArray     = [];
                $updateDataArray ['status'] = $this->errorState ;
                $this->updateRecord($item->getId(), $updateDataArray);
                 
                $customerId    = $item->getCustomerId();
                $email        = $item->getEmail();
                if ($customerId) {
                    $mgtCustomer = $this->_customerFactory->create()->load($customerId);
                    $checkSubscriber = $this->_subscriber->loadByCustomerId($customerId);
                     
                    $reportData = [];
                    $reportData['customer_id'] = $mgtCustomer->getId();
                    $reportData['email'] = $mgtCustomer->getEmail();
                    $reportData['group'] = $mgtCustomer->getGroupId();
                    $reportData['account_status'] = $mgtCustomer->getIsActive();
                    $reportData['is_subscribe'] = '0';
                    if ($checkSubscriber->isSubscribed()) {
                        $reportData['is_subscribe'] = '1';
                    }
                    $reportData ['status'] = $this->errorState ;
                     $reportId = $this->_bpcustomerreport->addRecord($reportData, 'return_id');
                     
                    $brightpearlUserId    = $this->_helperCustomer->postCustomerToBrightpearl($customerId);
					$this->segmetricHelper->postCustomerToSegmetric($customerId);
                    if ($brightpearlUserId) 
					{
                        $updateDataArray ['status'] = $this->completeState ;
                        $this->updateRecord($item->getId(), $updateDataArray);
                        
                        $reportData ['bp_customer_id'] = $brightpearlUserId;
                        $reportData ['status'] = $this->completeState ;
                        $this->_bpcustomerreport->updateRecord($reportId, $reportData);
                        $this->_subscriber->subscribeCustomerById($customerId);
						$reportData['is_subscribe'] = '1';
						$this->_bpcustomerreport->updateRecord($reportId, $reportData);	
						if($isDotmailerEnable)	{
							//$this->_dmSubscriberFactory->addCustomerInDotDigitalTradeProgram($mgtCustomer);						
							$this->_dmSubscriberFactory->addCustomerInDotDigitalAddressBook($mgtCustomer);						
						}
                    } else 
					{
                         $this->_bpcustomerreport->updateRecord($reportId, $reportData);
                    }
                }
            }
        }
    }
   	
    public function processQueueOld()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		
		$this->addCustomerFromCompanyCollection(); // ----- active record in queue		
        
        $this->cleanProcesQueue(); // ----- remove complete and not paid satus record from queue
        $this->updateStuckQueueRecord();  // ------- update stuck queue records
        
        if ($this->checkQueueProcessingStatus()) { // ------- check previous cron running or not
            return true;
        }
        $collection =   $this->create()->getCollection();
        $collection->addFieldToFilter('status', ['eq'=>$this->pendingState]);
        
        if (count($collection) > 0) {
            /* --------  update status =  processing status  ---------------*/
            foreach ($collection as $item) {
                $item->setState($this->processingState)->save();
            }
            /* --------  start processing  ---------------*/
            foreach ($collection as $item) {
                
                $customerInterface = $this->_objectManager->create('\Magento\Company\Model\CompanyManagement');
                $company = $customerInterface->getByCustomerId($item->getCustomerId());
                if ($company) {
                    if ($company->getStatus() != '1') {
                        $item->setState($this->pendingState)->save();
                        continue;
                    }
                } else {
                    $this->removeRecord($item->getId());
                    continue;
                }
                
                $updateDataArray     = [];
                $updateDataArray ['status'] = $this->errorState ;
                $this->updateRecord($item->getId(), $updateDataArray);
                 
                $customerId    = $item->getCustomerId();
                $email        = $item->getEmail();
                if ($customerId) {
                    $mgtCustomer = $this->_customerFactory->create()->load($customerId);
                    $checkSubscriber = $this->_subscriber->loadByCustomerId($customerId);
                     
                    $reportData = [];
                    $reportData['customer_id'] = $mgtCustomer->getId();
                    $reportData['email'] = $mgtCustomer->getEmail();
                    $reportData['group'] = $mgtCustomer->getGroupId();
                    $reportData['account_status'] = $mgtCustomer->getIsActive();
                    $reportData['is_subscribe'] = '0';
                    if ($checkSubscriber->isSubscribed()) {
                        $reportData['is_subscribe'] = '1';
                    }
                    $reportData ['status'] = $this->errorState ;
                     $reportId = $this->_bpcustomerreport->addRecord($reportData, 'return_id');
                     
                    $brightpearlUserId    = $this->_helperCustomer->postCustomerToBrightpearl($customerId);
                    if ($brightpearlUserId) {
                        $updateDataArray ['status'] = $this->completeState ;
                        $this->updateRecord($item->getId(), $updateDataArray);
                        
                        $reportData ['bp_customer_id'] = $brightpearlUserId;
                        $reportData ['status'] = $this->completeState ;
                        $this->_bpcustomerreport->updateRecord($reportId, $reportData);
                        $this->_subscriber->subscribeCustomerById($customerId);
						$reportData['is_subscribe'] = '1';
						$this->_bpcustomerreport->updateRecord($reportId, $reportData);	
						$this->_dmSubscriberFactory->addCustomerInDotDigitalTradeProgram($mgtCustomer);								
                    } else {
                         $this->_bpcustomerreport->updateRecord($reportId, $reportData);
                    }
                }
            }
        }
    }
    
    public function updateStuckQueueRecord()
    {
        $adminHours = 0;
        if (!$adminHours) {
            $adminHours = 2;
        }
          $collection = $this->create()->getCollection();
          $collection->addFieldToFilter('status', ['eq'=>$this->processingState]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $date_a = $this->_date->date($item->getUpdatedAt());
                $date_b = $this->_date->date();
                $diff = $date_a->diff($date_b)->format('%i');
                if ($diff >= $adminHours) {
                    $item->setState($this->pendingState)->save();
                }
            }
        }
        return true;
    }

    public function checkQueueProcessingStatus()
    {
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', [ 'eq'=>$this->processingState ]);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function cleanProcesQueue()
    {
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', ['in' => [$this->completeState,$this->errorNotPaid]]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $item->delete();
            }
        }
        //------------ clean already in send customer report ------------
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', ['in' => [ $this->pendingState, $this->errorState ]]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $data = $this->_bpcustomerreport->findRecord('customer_id', $item->getCustomerId());
                if ($data) {
                    $item->delete();
                }
            }
        }
    }


	public function removeNotConfigureGroupCustomerFromQueue()
	{
		$configurGroupids =  $this->_helperCustomer->getConfig('bpconfiguration/tradecustomer/groupid');
		$groupids = explode(",",$configurGroupids);

		$customerRepository = $this->_objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface');
		
		$collection =   $this->create()->getCollection();
		foreach ($collection as $item) 
		{
			$cid =  $item->getCustomerId();
			$loadCustomer = $customerRepository->getById($cid);
			$gid = $loadCustomer->getGroupId();
			if (!in_array($gid, $groupids))
			{
				$item->delete();				
			}			 
		}
	}


}
