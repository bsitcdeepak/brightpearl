<?php

namespace Bsitc\Brightpearl\Helper;

class CustomerHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_storeManager;
    protected $_api;
    protected $_logManager;
    protected $_customerFactory;
    protected $_addressFactory;
    protected $_countryFactory;
    protected $_reion;
    protected $_subscriber;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
		\Magento\Directory\Model\Region $reion,
        \Magento\Newsletter\Model\Subscriber $subscriber
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_api = $api;
        $this->_logManager = $logManager;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_countryFactory = $countryFactory;
        $this->_reion = $reion;
        $this->_subscriber = $subscriber;
        parent::__construct($context);
    }

    /**
     * Get store config
     */
    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
 
    public function recordLog($cat, $log_data, $title)
    {
        $logArray = [];
        if (!$cat) {
            $cat = "Global";
        }
         $logArray['category'] = $cat;
         $logArray['title'] =  $title;
         $logArray['store_id'] =  0;
         $logArray['error'] =  json_encode($log_data, true);
         $this->_logManager->addLog($logArray);
         return true;
    }
 
 
    
    public function getBpCustomerInfoArray($obj1, $telephone = "")
    {
            /*Call pricelist from configurations*/

            $priceListId = '';
        if ($this->getPricelist()) {
            $priceListId = $this->getPricelist();
        } else {
            $priceListId = '';
        }

        if ($telephone == "") {
            if (array_key_exists('telephone', $obj1)) {
                $telephone = $obj1['telephone'];
            }
             // $telephone = $obj1['telephone'];
        }

            $custInfo = [];
            $custInfo['salutation'] = '';
            $custInfo['firstName'] = $obj1['firstname'];
            $custInfo['lastName'] = $obj1['lastname'];
            $custInfo['postAddressIds'] = '';
            $custInfo['communication']['emails']['PRI'] = ['email' => $obj1['email']];
            $custInfo['communication']['telephones']['PRI'] = $telephone;
            $custInfo['communication']['websites']['PRI'] = '';
            $custInfo['marketingDetails']['isReceiveEmailNewsletter'] = true;
            $custInfo['financialDetails']['priceListId'] = $priceListId;
            return $custInfo;
    }

    /* Create a BP Address Arrays */
    public function getAddressArray($objAddress, $tmpObj = "")
    {
            $country = $this->_countryFactory->create()->loadByCode($objAddress['country_id']);
            $country = $country->getData();
            $isocode = $country['iso3_code'];
            $bpAddress     = [];
            
        if ($tmpObj) {
             $street =  $tmpObj->getStreet();
            if (is_array($street)) {
                $street1 = '';
                $street2 = '';
                if (array_key_exists("0", $street)) {
                    $street1 = trim($street[0]);
                }
                if (array_key_exists("1", $street)) {
                    $street2 = $street[1];
                }
                if ($street1 =="") {
                    $street1 = $objAddress['street'];
                }
                    
                $bpAddress['addressLine1'] = $street1;
                $bpAddress['addressLine2'] = $street2;
            } else {
                $bpAddress['addressLine1'] = $objAddress['street'];
            }
        } else {
             # Street
            $bpAddress['addressLine1'] = $objAddress['street'];
            # Suburb
            //$bpAddress['addressLine2'] = $objAddress->getStreet(2);
        }
            # City
            $bpAddress['addressLine3'] = $objAddress['city'];
            # County/State
            $bpAddress['addressLine4'] = $objAddress['region'];
            # Postcode/Zipcode
            $bpAddress['postalCode'] = $objAddress['postcode'];
            # LookupCountry
            $bpAddress['countryIsoCode'] = $isocode;
            return $bpAddress;
    }
    
    
    public function getDefaultBillingAddress()
    {
        $bpAddress     = [];
        $bpAddress['addressLine1'] = 'N/A';
        $bpAddress['addressLine2'] = '';
        $bpAddress['addressLine3'] = '';
        $bpAddress['addressLine4'] = '';
        $bpAddress['postalCode'] = '';
        $bpAddress['countryIsoCode'] = 'GBR';
        return $bpAddress;
    }
    
    
    public function postCustomerToBrightpearl($customerId)
    {
        $brightpearlUserId    = '';
        $mgtCustomer = $this->_customerFactory->create()->load($customerId);
        
        $email = $mgtCustomer->getEmail();
         
        $bpConfiguratinObj =  $this->getConfig('bpconfiguration/tradecustomer');
        $configObj = (array)$bpConfiguratinObj;
        
        $tmpCustomerArray = $this->createBrightpearlCustomerArray($mgtCustomer, $configObj) ;
        
        $this->recordLog("CUSTOMER", $tmpCustomerArray, 'Create Brightpearl Customer Array');
         
        $customerInfo     = $tmpCustomerArray['customer'];
        $contacttags     = $tmpCustomerArray['contactTags'];
        $attachment     = $tmpCustomerArray['attachment'];
        
        if ($this->_api->authorisationToken) 
		{
            $res = $this->_api->searchCustomerByEmail($email, $return_type = "object") ;
            if (isset($res->errors)) {
                 $this->recordLog("CUSTOMER", $res, 'Search Customer By Email result');
                return $brightpearlUserId;
            }
            $result = $res->response->results;
            $this->recordLog("CUSTOMER", $res, 'Check customer exist on BP');
             //  ---- if customer exist on brightperal ----
             
            $flagCustomerExist = false;
            usleep(1000000);
            if (count($result)) {
                foreach ($result as $_result) {
                    $res = $this->_api->getCustomerById($_result[0]);
                    if (isset($res['errors'])) {
                        $this->recordLog("CUSTOMER", $res, 'Get Customer By Id');
                        $brightpearlUserId = '';
                    }

                    if ($res['response'][0]['relationshipToAccount']['isStaff'] != 1 && $res['response'][0]['relationshipToAccount']['isSupplier'] != 1) {
                        $flagCustomerExist = true;
                        $brightpearlUserId = $res['response'][0]['contactId'];
                        $msg = 'customer exist on brightperal : ' . $brightpearlUserId;
                        $this->recordLog("CUSTOMER", $msg, 'Get Customer By Id');
                        break;
                    }
                }
            }
            $msg = 'flagCustomerExist : ' . $flagCustomerExist;  //  Insert Log
            $this->recordLog("CUSTOMER", $msg, 'Flag Customer Exist');

            if ($flagCustomerExist == false) {
                $billId = '';
                $delId = '';
                $Bil_Address = [];
                $Ship_Address = [];
                if (array_key_exists("BIL", $tmpCustomerArray)) {
                    $Bil_Address      = $tmpCustomerArray['BIL'];
                    $response     = $this->_api->postCustomerAddress($Bil_Address);
                    if (array_key_exists('response', $response)) {
                        $billId = $response['response'];
                    }
                    /* ---------- add retry code if not received response --------*/
                    if (!$billId && $billId == "") {
                        $response     = $this->_api->postCustomerAddress($Bil_Address);
                        if (array_key_exists('response', $response)) {
                            $billId = $response['response'];
                        }
                    }
                    /* ---------- add retry code if not received response --------*/
                     $this->recordLog("CUSTOMER", $response, 'Post customer billing address response');
                }
                
                if (array_key_exists("DEL", $tmpCustomerArray)) {
                    $Ship_Address     = $tmpCustomerArray['DEL'];
                    if (array_diff($Bil_Address, $Ship_Address)) {
                        $response = $this->_api->postCustomerAddress($Ship_Address);
                        if (array_key_exists('response', $response)) {
                            $delId = $response['response'];
                        }
                        /* ---------- add retry code if not received response --------*/
                        if (!$delId && $delId == "") {
                            $response = $this->_api->postCustomerAddress($Ship_Address);
                            if (array_key_exists('response', $response)) {
                                $delId = $response['response'];
                            }
                        }
                        $this->recordLog("CUSTOMER", $response, 'Post customer shipping address response');
                        /* ---------- add retry code if not received response --------*/
                    } else {
                        $delId = $billId;
                    }
                }else{
					$delId = $billId;
 				}
                 
                if (!$billId && $billId == "") {
                    $Bil_Address      = $this->getDefaultBillingAddress();
					$this->recordLog("CUSTOMER", $Bil_Address, 'getDefaultBillingAddress and Post customer ');
                    $response         = $this->_api->postCustomerAddress($Bil_Address);
                    if (array_key_exists('response', $response)) {
                        $billId = $response['response'];
                        if (!$delId && $billId == "") {
                            $delId = $billId;
                        }
                    }
                }
                
                 /* Update address id in customer info array */
                $customerInfo['postAddressIds'] = [
                    'DEF' => $billId,
                    'BIL' => $billId,
                    'DEL' => $delId
                ];
                
                /*POST Customer to brightpearl*/
                 $this->recordLog("CUSTOMER", $customerInfo, 'Post customer to BP request');
                                                    
                /*Post Customer to Bright Pearls*/
                $CreateUserResponse = $this->_api->postCustomer($customerInfo);
                $this->recordLog("CUSTOMER", $CreateUserResponse, 'Post customer to BP response');
                if (array_key_exists('response', $CreateUserResponse)) {
                    $brightpearlUserId = $CreateUserResponse['response'];
                    $this->postCustomerTag($brightpearlUserId, $contacttags);
                    $this->postCustomerCustomFileds($brightpearlUserId, $mgtCustomer, $attachment);
                }
            }
        }
        return $brightpearlUserId;
    }
	
	public function postCustomerCustomFileds($brightpearlUserId, $mgtCustomer, $attachment)
	{
		$this->recordLog("CUSTOMER Attachment", $attachment, $brightpearlUserId);
		if($attachment)
		{
			
				$mediaUrl =$this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
				$attachment = $mediaUrl.$attachment;
				$bpAttachmentCode =  $this->getConfig('bpconfiguration/bp_customerconfig/attachment', $mgtCustomer->getWebsiteId() );
				if(!$bpAttachmentCode){
					$bpAttachmentCode = 'PCF_ATCHMENT';
				}
				$data = '[
					{
					"op":    "add",
					"path":  "/'.$bpAttachmentCode.'",
					"value": "'.$attachment.'"
					}
				]';
				
				$response  = $this->_api->postCustomerCustomFiledsToBp($brightpearlUserId, $data);
				$log_data = "Customer Custom Filed ".json_encode($response);
				$this->recordLog("Customer Custom Filed ", $log_data, $brightpearlUserId);
		}
		return true;		
	}
    
    public function postCustomerTag($brightpearlUserId, $contacttags)
    {
        $this->recordLog("CUSTOMER", $brightpearlUserId, 'BP customer Id');
        $this->recordLog("CUSTOMER", $contacttags, 'BP Tag Id');
        if ($brightpearlUserId) {
            $postTagResponse = $this->_api->postCustomerTag($brightpearlUserId, $contacttags);
            $this->recordLog("CUSTOMER", $postTagResponse, 'Post customer TAG response');
        }
    }
    
    public function createBrightpearlCustomerArray($customer, $configObj)
    {
        
        $bpCustomerArray 	= [];
        $telephone 			= '';
        $organisationName	= '';
        $tradestatus        = 'TRADE';
        $contacttags        = '10';
        $is_subscribe		= false;
		$attachment			= '';
         
		/*
		$priceListId =  $this->getConfig('bpconfiguration/bp_customerconfig/defaultpricelist', $customer->getWebsiteId() );
		$defaultcurrency =  $this->getConfig('bpconfiguration/bp_customerconfig/defaultcurrency', $customer->getWebsiteId() );
		$leadesource =  $this->getConfig('bpconfiguration/bp_customerconfig/leadesource', $customer->getWebsiteId() );
		
		$tradePriceListId =  $this->getConfig('bpconfiguration/tradecustomer/pricelist', $customer->getWebsiteId() );
		$tradeCurrency =  $this->getConfig('bpconfiguration/tradecustomer/currency', $customer->getWebsiteId() );
		*/
		$priceListId =   $this->getConfig('bpconfiguration/tradecustomer/pricelist', $customer->getWebsiteId() );
		$currencyId =   $this->getConfig('bpconfiguration/tradecustomer/currency', $customer->getWebsiteId() );
		
        if (trim($priceListId) == "") {
            $priceListId = 3;
        }
 		
		
        if (trim($configObj['tradestatus'])!== "" || trim($configObj['tradestatus']) !== null) {
            $tradestatus = $configObj['tradestatus'];
        }
        if (trim($configObj['contacttags'])!== "" || trim($configObj['contacttags']) !== null) {
            $contacttags = $configObj['contacttags'];
        }
         
        /*---------- check if customer have billing address ----------------*/
        $country_id = 'OTHER'; //------------ 27-sep-2018 --------------
        $customerBillingAddressId = $customer->getDefaultBilling(); //oder getDefaultShipping
        if ($customerBillingAddressId) {
            $address = $this->_addressFactory->create()->load($customerBillingAddressId);
            $bpCustomerArray['BIL'] = $this->getAddressArray($address);  //  Billing Address info array
            $telephone = $address->getTelephone();
            $country_id = $address->getCountryId(); //------------ 27-sep-2018 --------------
        }
         /*---------- check if customer have shipping address ----------------*/
        $customerShippingAddressId = $customer->getDefaultShipping();
        if ($customerShippingAddressId) {
            $address = $this->_addressFactory->create()->load($customerShippingAddressId);
            $bpCustomerArray['DEL'] = $this->getAddressArray($address);  //  Shipping Address info array
            if ($telephone=="") {
                $telephone = $address->getTelephone();
            }
            $country_id = $address->getCountryId(); //------------ 27-sep-2018 --------------
        }

        // $company = $this->getDemoCompanyDetail();
        $company = $this->getCompanyDetail($customer->getId());
        $companyTaxNumber = '';
		if( $this->isCompany($customer->getId()) )
		{
			$this->recordLog("CUSTOMER", $company, 'isCompany true');
			if (!$customerBillingAddressId || !$customerShippingAddressId) {
				$bpCustomerArray['BIL'] = $this->getCompanyAddressArray($company);  //  Billing Address from Company address
				$bpCustomerArray['DEL'] = $bpCustomerArray['BIL'];
				$telephone     = $company['telephone'];
				$country_id = $company['country_id'];
				$attachment = $company['attach_file'];
				$companyTaxNumber =$company['vat_tax_id'];
			}
		}
		
        $organisationName  = $company['company_name'];
        
        $checkSubscriber = $this->_subscriber->loadByCustomerId($customer->getId());
        if ($checkSubscriber->isSubscribed()) {
            $is_subscribe = true;
        }
        /*---------- create customer info array  ----------------*/
        $custInfo = [];
        $custInfo['salutation']     = '';
        $custInfo['firstName']         = $customer->getFirstname();
        $custInfo['lastName']         = $customer->getLastname();
        $custInfo['postAddressIds'] = '';
        $custInfo['communication']['emails']['PRI'] = ['email' => $customer->getEmail()];
        $custInfo['communication']['telephones']['PRI'] = $telephone;
        
        /*
        if( $customer->getCustomerwebsite() and $customer->getCustomerwebsite()!= "" ){
            $custInfo['communication']['websites']['PRI'] =  $customer->getCustomerwebsite();
        }else{
            // $custInfo['communication']['websites']['PRI'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        }
        */

        $custInfo['marketingDetails']['isReceiveEmailNewsletter'] = $is_subscribe;
        $custInfo['financialDetails']['priceListId'] = $priceListId;
		if($currencyId){
			$custInfo['financialDetails']['currencyId'] = $currencyId;		
		}
		
		if ($companyTaxNumber) 
		{
			$custInfo['financialDetails']['taxNumber'] = $companyTaxNumber;
		}
		else
		{
			if ($customer->getTaxvat()) {
				$custInfo['financialDetails']['taxNumber'] = $customer->getTaxvat();
			}
		}

        //------------ 27-sep-2018 --------------
        $taxCodeId = $this->getCustomerTaxClassId($configObj, $customer->getGroupId(), $country_id);
        if ($taxCodeId) {
            $custInfo['financialDetails']['taxCodeId'] = $taxCodeId;
        }
        //------------ 27-sep-2018 --------------
        
        $custInfo['tradeStatus']  = $tradestatus;
        $custInfo['organisation']['name'] = $organisationName;
        $bpCustomerArray['customer'] = $custInfo;
        $bpCustomerArray['contactTags']  = $contacttags;
        $bpCustomerArray['attachment']  = $attachment;

        return $bpCustomerArray;
    }
    
    public function getCustomerTaxClassId($configObj, $group_id, $country_id)
    {
        $bp_customer_tax_class = "";
        $configureTaxMapping = $this->getConfigureCustomerTaxArray($configObj, $group_id);
        if (count($configureTaxMapping) > 0) {
            if (array_key_exists($country_id, $configureTaxMapping)) {
                $bp_customer_tax_class = $configureTaxMapping[$country_id];
            } else {
                $bp_customer_tax_class = $configureTaxMapping['OTHER'];
            }
        }
        return $bp_customer_tax_class;
    }
    
    //------------ 27-sep-2018 --------------
    public function getConfigureCustomerTaxArray($configObj, $group_id)
    {
        $configureTaxMapping = [];
        $gropupTaxArray = [];
        $taxmapping = $configObj['taxmapping'];
		if($taxmapping)
		{
			$taxmappingArray = explode("#", $configObj['taxmapping']);
			foreach ($taxmappingArray as $tmapping) {
				$tmappingArray = explode(":", $tmapping);
				$cid = $tmappingArray[0] ;
				$gid = $tmappingArray[1] ;
				$tcode = $tmappingArray[2] ;
				$configureTaxMapping[$gid][$cid] = $tcode;
			}
			if (array_key_exists($group_id, $configureTaxMapping)) {
				$gropupTaxArray = $configureTaxMapping[$group_id];
			}
		}
        return $gropupTaxArray;
    }
            
    
    
    public function isCompany($customerId)
    {
        
         $flag = false;
        $customerInterface = $this->_objectManager->create('\Magento\Company\Model\CompanyManagement');
        $company = $customerInterface->getByCustomerId($customerId);
        if ($company) {
            $flag = true;
        }
        return $flag;
    }
    
    
    public function getCompanyDetail($customerId)
    {
        
        $companyInfo = [];
        $customerInterface = $this->_objectManager->create('\Magento\Company\Model\CompanyManagement');
        $company = $customerInterface->getByCustomerId($customerId);
        if ($company) {
            $companyInfo['status']             = $company->getStatus();
            $companyInfo['id']                 = $company->getId();
            $companyInfo['company_name']     = $company->getCompanyName();
            $companyInfo['legal_name']         = $company->getLegalName();
            $companyInfo['company_email']     = $company->getCompanyEmail();
            $companyInfo['vat_tax_id']         = $company->getVatTaxId();
            $companyInfo['reseller_id']     = $company->getResellerId();
            $street                         = $company->getStreet();
            $companyInfo['street_0']         = $street[0];
            $companyInfo['street_1']         = @$street[1];
            $companyInfo['city']             = $company->getCity();
            $companyInfo['country_id']         = $company->getCountryId();

			if($company->getRegionId())	{
				// $region = $this->_objectManager->create('Magento\Directory\Model\Region')->load( $company->getRegionId() );
				$region = $this->_reion->load( $company->getRegionId() );
				$companyInfo['region'] = $region->getName();
			}else{
				$companyInfo['region'] = $company->getRegion();
			}

            $companyInfo['postcode']         = $company->getPostcode();
            $companyInfo['telephone']         = $company->getTelephone();
            $companyInfo['attach_file']         = $company->getAttachFile();
            /*
            $customerRepository = $objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface');
            $companyAttributes = $customerRepository->getById($mgtCustomer->getId())->getExtensionAttributes()->getCompanyAttributes();
            $companyInfo['job_title'] = $companyAttributes->getJobTitle();
            */
        }else{
			
            $companyInfo['status'] = '';
            $companyInfo['id'] = '';
            $companyInfo['company_name'] = '';
            $companyInfo['legal_name'] = '';
            $companyInfo['company_email'] = '';
            $companyInfo['vat_tax_id'] = '';
            $companyInfo['reseller_id'] = '';
            $street = '';
            $companyInfo['street_0'] = 'NA';
            $companyInfo['street_1'] = 'NA';
            $companyInfo['city'] = '';
            $companyInfo['country_id'] = 'GB';
            $companyInfo['region'] = '';
            $companyInfo['postcode'] = '';
            $companyInfo['telephone'] = '';
            $companyInfo['attach_file'] = '';
		}
        return $companyInfo;
    }

    public function getDemoCompanyDetail()
    {
        
         $companyInfo = [];
        $companyInfo['status']             = 0;
        $companyInfo['id']                 = 10;
        $companyInfo['company_name']     = 'BSITC';
        $companyInfo['legal_name']         = 'BSITC Legal Name';
        $companyInfo['company_email']     = 'bsitc@businesssolutionsinthecloud.com';
        $companyInfo['vat_tax_id']         = 'GB4209211';
        $companyInfo['reseller_id']     = '4209211';
        $companyInfo['street_0']         = '1 High Street';
        $companyInfo['street_1']         = 'Suburb - Street 2';
        $companyInfo['city']             = 'London';
        $companyInfo['country_id']         = 'GB';
        $companyInfo['region']             = 'Greater London';
        $companyInfo['postcode']         = 'W129LF ';
        $companyInfo['telephone']         = '07833723386';
        $companyInfo['attach_file']         = '';
        return $companyInfo;
    }

    public function getCompanyAddressArray($companyInfo)
    {
        $country = $this->_countryFactory->create()->loadByCode($companyInfo['country_id']);
        $country = $country->getData();
        $isocode = $country['iso3_code'];
        $bpAddress     = [];
        # Street
        $bpAddress['addressLine1']     = $companyInfo['street_0'];
        # Suburb
        $bpAddress['addressLine2']     = $companyInfo['street_1'];
        # City
        $bpAddress['addressLine3']     = $companyInfo['city'];
        # County/State
        $bpAddress['addressLine4']     = $companyInfo['region'];
        # Postcode/Zipcode
        $bpAddress['postalCode']     = $companyInfo['postcode'];
        # LookupCountry
        $bpAddress['countryIsoCode'] = $isocode;
        return $bpAddress;
    }
}
