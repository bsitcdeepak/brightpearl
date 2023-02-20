<?php

/**
 * Brightpearl API
 *
 * Brightpearl API class
 * @package brightpearl
 * @version 2.0
 * @author Vijay Vishvkarma
 * @email vijay1982.msc@gmail.com
 */

    
namespace Bsitc\Brightpearl\Model;

class Dotmalier extends \Magento\Framework\Model\AbstractModel
{
    public $enable;
    public $apiUrl;
    public $apiUser;
    public $apiPassword;
    public $addressBookId;
    public $programId;
    
    public $ch;
    public $headerArray;
    public $apiResponse;
    public $apiError;
    
    public $_scopeConfig;
    public $_storeManager;
    public $_objectManager;
    public $_logManager;
    public $_data;
    public $_date;
 
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Logs $logManager
    ) {
		$this->_objectManager = $objectManager;
		$this->_storeManager = $storeManager;
		$this->_scopeConfig = $scopeConfig;
		$this->_logManager = $logManager;
		$this->_date = $date;
		$this->configure();
    }
    
    protected function getDmConfiguration()
    {
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$data['enable'] = $this->_scopeConfig->getValue('bpconfiguration/dotmailer/enable', $storeScope);
		$data['api_url'] = $this->_scopeConfig->getValue('bpconfiguration/dotmailer/api_url', $storeScope);
		$data['api_username'] = $this->_scopeConfig->getValue('bpconfiguration/dotmailer/api_username', $storeScope);
		$data['api_password'] = $this->_scopeConfig->getValue('bpconfiguration/dotmailer/api_password', $storeScope);
		$data['address_book_id'] = $this->_scopeConfig->getValue('bpconfiguration/dotmailer/address_book_id', $storeScope);
		$data['programid'] = $this->_scopeConfig->getValue('bpconfiguration/dotmailer/programid', $storeScope);
		$this->_data = $data;
    }

    protected function configure()
    {
        $this->getDmConfiguration();
        $this->enable = trim($this->_data['enable']);
        $this->apiUrl = trim($this->_data['api_url']);
        $this->apiUser = trim($this->_data['api_username']);
        $this->apiPassword = trim($this->_data['api_password']);
        $this->addressBookId = trim($this->_data['address_book_id']);
        $this->programId = trim($this->_data['programid']);
		$header = array('Accept: application/json','Content-Type: application/json');
		$this->headerArray 	= $header;
		$this->ch = curl_init();
    }

    public function recordLog($log_data, $title = "DM API")
    {
         $logArray = [];
         $logArray['category'] = 'Global';
         $logArray['title'] =  $title;
         $logArray['store_id'] =  1;
         $logArray['error'] =  json_encode($log_data, true);
         $this->_logManager->addLog($logArray);
          return true;
    }
     
    public function getCommonResponse($url, $method = 'POST', $data = '', $json = true, $callTitle = '')
    {
		usleep(100000); /* 1 sec = 1000000 microseconds  */
        if ($callTitle != 'Post Create Webhook' and $data!="") {
            $data = $json ? json_encode($data, true) : http_build_query($data);
        }		
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headerArray);
        if ($data && $data!="") {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        }
		curl_setopt($this->ch, CURLAUTH_BASIC, CURLAUTH_DIGEST);
		curl_setopt($this->ch, CURLOPT_USERPWD, $this->apiUser . ':' . $this->apiPassword);
		
        $response = $this->executeQuery();
        $errorCheck = $this->checkErrorsInCall($response, $callTitle);
        if ($errorCheck) {
            $response = $this->executeQuery(); /* ------- if error in call then try again first -------- */
            $errorCheck = $this->checkErrorsInCall($response, $callTitle);
            if ($errorCheck) {
                $response = $this->executeQuery(); /* ------- if error in call then try again second -------- */
            }
        }
         $this->apiResponse =  $response ;
         return $response ;
    }
    
    public function executeQuery()
    {
        $response = curl_exec($this->ch);
        return $response;
    }
    
    public function checkErrorsInCall($response, $callTitle = '')
    {
        $responseCode = (int) curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if (false === $response || $responseCode != '200') {
            $errmsg =  curl_error($this->ch);
            $this->apiError = json_encode($errmsg, true);
            if ($errmsg) {
                $this->recordLog($errmsg, 'DM API Error');
            }
            return true;
        }
        if (preg_match("/\bmany requests\b/i", $response) || preg_match("/\bYou have sent too many requests\b/i", $response)) {
            return true;
        }
        return false;
    }

    /*
    *  Post Enrolment in dotmailer programs
    */
	
	public function postEnrolment($email)
	{
		$contacts = $this->getDotmailerContactId($email);
		if(count($contacts )>0)
		{
			$content = [];
			$content['ProgramId'] = $this->programId;
			$content['Status'] = '';
			$content['DateCreated'] = $this->_date->date()->format('Y-m-d H:i:s');
			$content['Contacts'] = $contacts;
			
			$url 	= $this->apiUrl."/v2/programs/enrolments";
			$method		= 'POST';
			$data		= $content;
			$json		= true;
			$callTitle 	= 'Post Enrolment';
			$result 	= $this->getCommonResponse($url, $method, $data, $json, $callTitle);
			$response 	= json_decode($result, true);
			if(array_key_exists( "id", $response)){
				return $response;
			}else{
				$error = array();
				$error['error'] = $response;
				return $error;
			}
		}else{
			$error = array();
			$error['error'] = 'Unable to get dotmailer customer id from email '.$email;
			return $error;
		}
  	}	
	
	
    /*
    *  Post Contact in dotmailer Adddress Book
    */
	
	public function postContactInaddressBook($content)
	{
		$url 		= $this->apiUrl."/v2/address-books/".$this->addressBookId."/contacts/";
		$method		= 'POST';
		$data		= $content;
		$json		= true;
		$callTitle 	= 'Post Contact in Adddress Book';
		$result 	= $this->getCommonResponse($url, $method, $data, $json, $callTitle);
		$response 	= json_decode($result, true);
		if(array_key_exists( "id", $response)){
			return $response;
		}else{
			$error = array();
			$error['error'] = $response;
			return $error;
		}
  	}	
	
 
	public function getDotmailerContactId($email)
	{
		$contactsArray = array();
        $url		= $this->apiUrl."/v2/contacts/".$email;
        $method		= 'GET';
        $data		= [];
        $json		= true;
        $callTitle	= 'Get Dotmailer Contact Id By Email';
        $response 	= $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        $result 	= json_decode($response, true);
		if ($result and array_key_exists( "id", $result) and  $result['email'] == $email ){
			$contactsArray[] = $result['id'];
		} 
 		return $contactsArray;
 	}
 	
	public function __destruct(){
 		curl_close($this->ch);
 	}
}