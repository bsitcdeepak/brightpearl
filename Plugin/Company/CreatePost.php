<?php

namespace Bsitc\Brightpearl\Plugin\Company;

class CreatePost
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyFactory $companyFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Newsletter\Model\Subscriber $subscriber,
		\Magento\Framework\Api\DataObjectHelper $objectHelper,
		\Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
		\Bsitc\Brightpearl\Model\Logs $logManager
    ) {
        $this->companyFactory = $companyFactory;
        $this->customerFactory = $customerFactory;
        $this->_subscriber = $subscriber;
        $this->_logManager = $logManager;
        $this->objectHelper = $objectHelper;
		$this->customerDataFactory = $customerDataFactory;
		$this->customerAccountManagement = $customerAccountManagement;	
    }

    public function aroundExecute(\Magento\Company\Controller\Account\CreatePost $subject, callable $proceed)
    {           
        $request = $subject->getRequest();
        $data = $request->getParams();
		// $this->recordLog($data);
        $email = $data['company']['company_email'];
		$is_subscribed = 0 ;
		if (array_key_exists("is_subscribed",$data)) {
			 $is_subscribed = $data['is_subscribed'];
		}
        $returnValue = null;
        $returnValue = $proceed();
        $company = $this->companyFactory->create()->load($email, 'company_email');
        if ($company) 
		{
            $customer = $this->customerFactory->create()->load($company->getSuperUserId());
            if ($customer->getId()) 
			{
				if($is_subscribed){
					$this->_subscriber->subscribeCustomerById($customer->getId());
				// 	$this->_subscriber->unsubscribeCustomerById($customer->getId());
 				}
            }
        }
        return $returnValue;
    }

    public function disableNewsletterSubscription($customer)
    {
        $checkSubscriber = $this->_subscriber->loadByCustomerId( $customer->getId() );
		//$this->recordLog($checkSubscriber->isSubscribed());
		if ($checkSubscriber->isSubscribed()) {
			$this->_subscriber->unsubscribeCustomerById($customer->getId());
			//$this->recordLog('unsubscribe');
		}
		return true;		
    }

    public function recordLog($log_data)
    {
        $logArray = [];
		$logArray['category'] = 'TEST-2';
		$logArray['title'] =  'Company Create Post';
		$logArray['store_id'] =  0;
		$logArray['error'] =  json_encode($log_data, true);
		$this->_logManager->addLog($logArray);
		return true;
    }
	
}