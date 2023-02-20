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

class Api extends \Magento\Framework\Model\AbstractModel
{
    public $apiUser;
    public $apiEmail;
    public $apiPassword;
    public $apiDcCode;
    public $apiVersion;
    public $apiService;
    
    public $devToken;
    public $devSecrete;
    public $appToken;
    public $devRef;
    public $appRef;
    public $privateApp;
    
    public $ch;

    public $authenticationDetails;
    public $authenticationURL;
    public $authorisationToken;
    public $brightURL;
    
    public $enable;
    public $moduleStatus;
    public $apiResponse;
    public $msgError;
    
    public $_scopeConfig;
    public $_storeManager;
    public $_objectManager;
    public $_logManager;
    public $_data;
    
    public $bp_so_config;
    public $bp_sc_config;
    public $bpcron;


 
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Logs $logManager
    ) {
            
         $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_logManager = $logManager;
        $this->configure();
        //$this->getBpConfiguration();
    }
    
    protected function getBpConfiguration()
    {
    
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
         $data['enable']                = $this->_scopeConfig->getValue('bpconfiguration/api/enable', $storeScope);
        $data['bp_useremail']        = $this->_scopeConfig->getValue('bpconfiguration/api/bp_useremail', $storeScope);
        $data['bp_account_id']         = $this->_scopeConfig->getValue('bpconfiguration/api/bp_account_id', $storeScope);
        $data['bp_password']         = $this->_scopeConfig->getValue('bpconfiguration/api/bp_password', $storeScope);
        $data['bp_dc_code']         = $this->_scopeConfig->getValue('bpconfiguration/api/bp_dc_code', $storeScope);
        $data['bp_api_version']     = $this->_scopeConfig->getValue('bpconfiguration/api/bp_api_version', $storeScope);
        $data['bp_api_service']     = $this->_scopeConfig->getValue('bpconfiguration/api/bp_api_service', $storeScope);
        $data['bpcron_enable']        = $this->_scopeConfig->getValue('bpconfiguration/bpcron/enable', $storeScope);
    
        $data['privateapp']            = $this->_scopeConfig->getValue('bpconfiguration/api/privateapp', $storeScope);
        $data['devtoken']            = $this->_scopeConfig->getValue('bpconfiguration/api/devtoken', $storeScope);
        $data['devsecrete']            = $this->_scopeConfig->getValue('bpconfiguration/api/devsecrete', $storeScope);
        $data['apptoken']            = $this->_scopeConfig->getValue('bpconfiguration/api/apptoken', $storeScope);
        $data['devref']                = $this->_scopeConfig->getValue('bpconfiguration/api/devref', $storeScope);
        $data['appref']                = $this->_scopeConfig->getValue('bpconfiguration/api/appref', $storeScope);
        
        $this->_data                = $data;
        $this->bp_so_config            = $this->_scopeConfig->getValue('bpconfiguration/bp_so_config', $storeScope);
        $this->bp_sc_config            = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config', $storeScope);
        $this->bpcron                = $this->_scopeConfig->getValue('bpconfiguration/bpcron', $storeScope);
    }
 

    protected function configure()
    {
    
        $this->getBpConfiguration();
         $this->ch = curl_init();
        $error = false;
        
        $this->enable         = trim($this->_data['enable']);
        $this->apiUser         = trim($this->_data['bp_account_id']);
        $this->apiEmail     = trim($this->_data['bp_useremail']);
        $this->apiPassword     = trim($this->_data['bp_password']);
        $this->apiDcCode     = trim($this->_data['bp_dc_code']);
        $this->apiVersion     = trim($this->_data['bp_api_version']);
        $this->apiService     = trim($this->_data['bp_api_service']);
        
        $this->privateApp   =   trim($this->_data['privateapp']);
        $this->devToken       =   trim($this->_data['devtoken']);
        $this->devSecrete   =   trim($this->_data['devsecrete']);
        $this->appToken       =   trim($this->_data['apptoken']);
        $this->devRef       =   trim($this->_data['devref']);
        $this->appRef       =   trim($this->_data['appref']);
        
        $this->brightURL             = 'https://ws-'.$this->apiDcCode.'.brightpearl.com/'.$this->apiVersion.'/'.$this->apiUser;
        $this->authenticationURL     ='https://ws-'.$this->apiDcCode.'.brightpearl.com/'.$this->apiUser.'/authorise';
        $this->authenticationDetails = ['apiAccountCredentials' => [
               'emailAddress' => $this->apiEmail,
               'password'     => $this->apiPassword,
               ]
           ];
        /* -------- private app method enable -------------*/
        if (isset($this->_data['privateapp']) and $this->_data['privateapp'] == true) {
             $this->authorisationToken = 'authorisationByPrivateApp';
        } else {
            /* -------- authentication method enable -------------*/
            if (!isset($this->_data['enable']) || !isset($this->_data['bp_useremail']) || !isset($this->_data['bp_password']) || !isset($this->_data['bp_account_id']) || !isset($this->_data['bp_dc_code']) || !isset($this->_data['bp_api_version'])) {
                $this->authorisationToken = '';
            } else {
                $this->authorize();
            }
        }
    }
    
    public function authorize()
    {
        $url         = $this->authenticationURL;
        $method        = 'POST';
        $data        = $this->authenticationDetails;
        $json        = true;
        $callTitle    = 'Authorize';
        $res           =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        $result     = json_decode($res, true);
        if (isset($result) && array_key_exists("response", $result)) {
            $this->authorisationToken = $result['response'];
        }
    }

    public function recordLog($log_data, $title = "API")
    {
         $logArray = [];
         $logArray['category'] = 'Global';
         $logArray['title'] =  $title;
         $logArray['store_id'] =  1;
         $logArray['error'] =  json_encode($log_data, true);
         $this->_logManager->addLog($logArray);
          return true;
    }
     
    public function getHeader()
    {
         
        if ($this->privateApp) {
             $authToken = base64_encode(hash_hmac("sha256", $this->appToken, $this->devSecrete, true));
            $header = [
                'Content-Type:application/json;charset=UTF-8',
                'brightpearl-dev-ref:'.$this->devRef,
                'brightpearl-app-ref:'.$this->appRef,
                'brightpearl-account-token:'.$authToken,
            ];
        } else {
            $header = [];
            if ($this->authorisationToken) {
                 $header[]    = "brightpearl-auth: ".$this->authorisationToken;
            }
            $header[] = 'Content-Type: application/json';
        }
        
        return $header;
    }
    
    public function getCommonResponse($url, $method = 'POST', $data = '', $json = true, $callTitle = '')
    {
        usleep(300000); /* 1 sec = 1000000 microseconds  */
        if ($callTitle != 'Post Create Webhook' and $data!="") {
            $data = $json ? json_encode($data, true) : http_build_query($data);
        }
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->getHeader());
        if ($data && $data!="") {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        }
		$response = $this->executeQuery();
		$errorCheck = $this->checkErrorsInCall($response, $callTitle);
        if ($errorCheck) 
		{
			if($callTitle == 'postOrderWithItems')
			{
				$data =  json_decode($data, true) ;
				$customerRef = $data['ref'];
				$orderId = $this->searchSalesOrderByCustomerRef($customerRef);
				if($orderId){
					$response = [];
					$response['response'] = $orderId;
					$this->apiResponse =  json_encode($response,true);
					return json_encode($response,true);
				}else{
					$response = $this->executeQuery(); /* ------- if error in call then try again first -------- */
					$errorCheck = $this->checkErrorsInCall($response, $callTitle);
					if ($errorCheck) {
						
						/* ------- if error in call then try again second -------- */

							if($callTitle == 'postOrderWithItems')
							{
								$customerRef = $data['ref'];
								$orderId = $this->searchSalesOrderByCustomerRef($customerRef);
								if($orderId){
									$response = [];
									$response['response'] = $orderId;
									$this->apiResponse =  json_encode($response,true);
									return json_encode($response,true);
								}else{
									$response = $this->executeQuery(); /* ------- if error in call then try again first -------- */
									$errorCheck = $this->checkErrorsInCall($response, $callTitle);
									if ($errorCheck) {
										$response = $this->executeQuery(); /* ------- if error in call then try again second -------- */
									}
								}
							}
							else
							{
								$response = $this->executeQuery(); /* ------- if error in call then try again first -------- */
								$errorCheck = $this->checkErrorsInCall($response, $callTitle);
								if ($errorCheck) {
									$response = $this->executeQuery(); /* ------- if error in call then try again second -------- */
								}
							}						
 						
						/* ------- if error in call then try again second -------- */
 					}
 				}
			}
			else
			{
				$response = $this->executeQuery(); /* ------- if error in call then try again first -------- */
				$errorCheck = $this->checkErrorsInCall($response, $callTitle);
				if ($errorCheck) {
					$response = $this->executeQuery(); /* ------- if error in call then try again second -------- */
				}
			}
        }
		$this->apiResponse =  $response ;
		return $response ;
    }
    
      /*Add Patch Request*/
    public function getCommonResponsePatch($url, $method, $data, $json, $callTitle)
    {
           curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        $response = $this->executeQuery();
        
        $errorCheck = $this->checkErrorsInCall($response, $callTitle);
        if ($errorCheck) {
            $response = $this->executeQuery(); /* ------- if error in call then try again first -------- */
             $errorCheck = $this->checkErrorsInCall($response, $callTitle);
            if ($errorCheck) {
                $response = $this->executeQuery(); /* ------- if error in call then try again second -------- */
            }
        }
        
        return $response ;
    }



    public function getCommonResponse_new($arr, $url, $method = 'POST', $encodeJson = true, $resultJson = true)
    {
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
             curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->getHeader());
        if ($encodeJson) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($arr));
        } else {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $arr);
        }
            $response = $this->executeQuery();
            $errorCheck = $this->checkErrorsInCall($response);
        if (!$errorCheck) {
            $response = $this->executeQuery();
        }
            
        if ($resultJson) {
            return json_decode($response);
        } else {
            return $response;
        }
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
                $this->recordLog($errmsg, 'API Error');
            }
            $this->recordLog($response, 'API Response '.$callTitle);
            return true;
        }
        if (preg_match("/\bmany requests\b/i", $response) || preg_match("/\bYou have sent too many requests\b/i", $response)) {
            return true;
        }
        return false;
    }

    /*
    * fetch Products Options by Mick
    */
    public function getProductOption()
    {
        $url         = $this->brightURL . '/product-service/option/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Options';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }


    /*
    * fetch Products Bundles
    */
    public function getProductBundle($pid)
    {
        $url         = $this->brightURL . '/product-service/product/'.$pid.'/bundle';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Bundle Products';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }


    
    /*
    * fetch Products Options value by Mick
    */
    public function getProductOptionValue($id)
    {
        
        $url         = $this->brightURL . '/product-service/option/'.$id.'/value';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Options Values';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * fetch Products Brands by Mick
    */
    public function getProductBrand()
    {
        $url         = $this->brightURL . '/product-service/brand/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Brands';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    
    /*
    * fetch Products Categories BY Mick
    */
    public function getProductCategory()
    {
        $url         = $this->brightURL . '/product-service/brightpearl-category/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Categories';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        
        return json_decode($response, true);
    }
    


    /*
    * fetch channel Name BY Mick
    */
    public function getChannelName()
    {
        $url         = $this->brightURL . '/product-service/channel/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Channel Name';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        
        return json_decode($response, true);
    }

    /*
    * fetch products by products ID Mick
    */
    public function getProductById($productid, $return_type = "")
    {
        $url         = $this->brightURL . '/product-service/product/' . $productid.'?includeOptional=customFields,nullCustomFields';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Product By ID';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         
        if ($return_type == 'object') {
            return json_decode($response);
        } else {
            return json_decode($response, true);
        }
    }
 
    
    
    /*
    * fetch products by products ids BY Mick
    */
    public function getAllProductbyCustomAttribute($range)
    {
        $url         = $this->brightURL . '/product-service/product/'.$range;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Products By ID';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * Fetch product by product search API
    */
    public function getSearchProducts()
    {
        //$url         = $this->brightURL . '/product-service/product-search?productId=10333';
        $url         = $this->brightURL . '/product-service/product-search?productGroupId=0';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Products which are searched';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    
    
    /*Get Inventory in products API*/
    public function fetchProductStock($poid)
    {
        $url =$this->brightURL.'/warehouse-service/product-availability/'.$poid.'?includeOptional=breakDownByLocation';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Warehouse Inventory';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

        /*Get Product Search by SKU New*/
    public function getProductIDFromSku($sku)
    {
        $url         = $this->brightURL . '/product-service/product-search/?SKU=' . $sku;
        $method     = 'GET';
        $data         = [];
        $json        = 'no_change';
        $callTitle    = 'Search by SKU';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        $data = json_decode($response);
        if (!empty($data->response->results) && isset($data->response->results[0][0])) {
                return $data->response->results[0][0];
        } else {
            return false;
        }
    }

        /*
        * Post Product Images
        */
    public function postProductImagetoBp($pid, $data)
    {
           $url         = $this->brightURL.'/product-service/product/'.$pid.'/custom-field';
        $method     = 'PATCH';
        $data         = $data;
        $json        = true;
        $callTitle    = 'Post Products Images to BP';
        return $result     =  $this->getCommonResponsePatch($url, $method, $data, $json, $callTitle);
         //$result     = $this->getCommonResponse($url, $method, $data,  $json, $callTitle);
         //return json_decode($result, false);
    }


    public function postInventoryReservation($bpOrderId, $warehouse, $data, $return_type = "")
    {
        
        $url         = $this->brightURL . '/warehouse-service/order/' . $bpOrderId . '/reservation/warehouse/' . $warehouse;
        $method     = 'POST';
        $json        = 'no_change';
        $callTitle    = 'Post Create Webhook';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return $response;
    }
        
        
        
        /*Inventory Reservation*/
    public function checkInventoryReservation($bpOrderId, $data, $return_type = "")
    {
        $url         = $this->brightURL . '/warehouse-service/order/' . $bpOrderId . '/reservation';
        $method     = 'GET';
        $json        = 'no_change';
        $callTitle    = 'Post Create Webhook';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return $response;
    }
 
    public function getFirstSearchProducts()
    {
        $url         = $this->brightURL . '/product-service/product-search';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Products which are searched';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        $res = json_decode($response, true);

        if (array_key_exists('response', $res)) {
            if (array_key_exists('results', $res['response'])) {
                $collections = $res['response']['results'];
                foreach ($collections as $collection) {
                        $id =  $collection[0];
                        break;
                }
            }
        }

        if ($id) {
            return $id;
        }
        //return json_decode($response, true);
    }

    /*****************Products Search API for Products*/
    public function productGetUri($url)
    {
        $url =$this->brightURL.'/product-service'.$url."?includeOptional=customFields";
         $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->getHeader());
        $response = $this->executeQuery();
        $this->_datahelper->getAllproducts();
        //return json_decode($response);
    }

    public function fetchProducts()
    {
        $url =$this->brightURL.'/product-service/product';
         $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
          curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->getHeader());
         $response = $this->executeQuery();
        $data = json_decode($response);
        if ($data->response->getUris) {
            foreach ($data->response->getUris as $key => $value) {
                $this->product[] = $value;
                //$this->product[] = $this->productGetUri($value);
            }
        }
            return $this->product;
    }

    
    /*
    * fetch products Price List BY Mick
    */
    public function getProductPriceListNew($range)
    {
        $url         = $this->brightURL . '/product-service/product-price/'.$range;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Products Price List';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    
    /*
    * fetch products Price List BY Mick
    */
    //public function getProductPriceList($range) {

    public function getProductPriceList($id)
    {
        $url         = $this->brightURL . '/product-service/product-price/'.$id;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Products Price List';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }



    public function getAllProductFromUriAttribute($uri)
    {
        $value        = $uri;
        $url         = $this->brightURL . '/product-service/product/'.$value;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Products BY Using Range 1';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }




    /*
    * fetch products by products Uri;s
    */
    public function getAllProductFromUri($uri)
    {
        $value        = $uri;
        $url         = $this->brightURL . '/product-service'.$value;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Products BY Using Range 2';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    /*
    * fetch Products Custom Fields(Attributes) by Mick
    */
    
    public function getProductByCustomFields($range)
    {
        $url         = $this->brightURL . '/product-service/product-custom-field/'.$range;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Custom Fields 1';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }



    public function getCustomFields()
    {
        $url         = $this->brightURL . '/product-service/product/custom-field-meta-data';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Custom Fields';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }




    /*Products custom fields for Single products*/
    public function getProductBySingleCustomField($productid)
    {
        $url         = $this->brightURL . '/product-service/product-custom-field/'.$productid;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Custom Fields 2';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * fetch products by products From Range BY Mick
    */
    public function getAllProductFromRange($range)
    {
        $url         = $this->brightURL . '/product-service/product/'.$range;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Products BY Using Range 3';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * search bright pearl customer by email
    */
    public function searchCustomerByEmail($email, $return_type = " ")
    {
        $url         = $this->brightURL . '/contact-service/contact-search?primaryEmail=' . $email;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Search Customer By Email';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);

        if ($return_type == 'object') {
            return json_decode($response);
        } else {
            return json_decode($response, true);
        }
    }
    
    /*
    * get bright pearl customer by id
    */
    public function getCustomerById($id)
    {
        $url         = $this->brightURL . '/contact-service/contact/' . $id;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Customer By Id';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * post customer to bright pearl
    */
    public function postCustomer($data)
    {
        $url         = $this->brightURL . '/contact-service/contact/';
        $method     = 'POST';
        $json        = true;
        $callTitle    = 'Post Customer';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    /*
    * post customer address to bright pearl
    */
    public function postCustomerAddress($data)
    {
        $url         = $this->brightURL . '/contact-service/postal-address/';
        $method     = 'POST';
        $json        = true;
        $callTitle    = 'Post Customer Address';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    public function postCustomerTag($customer, $tag)
    {
        $url         = $this->brightURL . '/contact-service/contact/'.$customer.'/tag/'.$tag;
        $method     = 'POST';
        $json        = true;
        $data       = [];
        $callTitle    = 'Post Customer Tag';
        $response     = $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    /*
    * get supplier by id
    */
    public function getSupplier($id)
    {
        $url         = $this->brightURL.'/contact-service/contact/'.$id.'?includeOptional=customFields';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Supplier By Id';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    /*
    * get all suppliers
    */
    public function getAllSupplier()
    {
        $url         = $this->brightURL.'/contact-service/contact-search?isSupplier=true&';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Supplier';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    /*
    * get updated suppliers
    */
    public function getUpdatedSupplier($day)
    {
        $days         = '-'.$day.' days';
        $daysPlus         = '+'.$day.' days';
        $fromDate      = date('Y-m-d\TG:i:s', strtotime($days));
        $toDate        = date('Y-m-d\TG:i:s', strtotime($daysPlus));
        $url         = $this->brightURL.'/contact-service/contact-search?isSupplier=true&updatedOn='.$fromDate.'/'.$toDate;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Supplier';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    /*
    * get Exchange Rate
    */
    public function getExchangeRate()
    {
         $url         = $this->brightURL.'/accounting-service/exchange-rate';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Exchange Rate';
        $response     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    /*
    * get All Brands
    */
    public function getAllBrands()
    {
         $url         = $this->brightURL.'/product-service/brand/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Brands';
        $response     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        $result     = json_decode($response, true);
        if (array_key_exists("response", $result)) {
            return $result['response'];
        } else {
            return '';
        }
    }

    /*
    * get All Channel
    */
    public function getAllChannel()
    {
         $url         = $this->brightURL.'/product-service/channel/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Channel';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * get All Lead Source
    */
    public function getAllLeadSource()
    {
         $url         = $this->brightURL.'/contact-service/lead-source/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Lead Source';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    /*
    * get Shipping Methods
    */
    public function getAllBpShippingMethods()
    {
         $url         = $this->brightURL.'/warehouse-service/shipping-method';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Shipping Methods';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

 
    /*
    * get Order Shipping Status
    */
    public function getAllBpOrderShippingMethods()
    {
         $url         = $this->brightURL.'/order-service/order-shipping-status/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Order Shipping Methods';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
     
     
    /*
    * get Sales Credit from id
    */
    public function getSalesCreditById($id)
    {
    
         $url         = $this->brightURL.'/order-service/sales-credit/'.$id;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Sales Credit from Id';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * get All Sales Credit Order
    */
    public function getAllSalesCredits()
    {

         $url         = $this->brightURL.'/order-service/order-search/?orderTypeId=3';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Sales Credit orders';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * get Customer Payment Search
    */
    public function searchCustomerPaymentFormOrderId($id)
    {
    
    
         $url         = $this->brightURL.'/accounting-service/customer-payment-search?orderId='.$id;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Customer Payment Search for order  #'.$id;
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * get sale-payment-total
    */
    public function getSalePaymentTotal($id)
    {
     
         $url         = $this->brightURL.'/accounting-service/sale-payment-total/'.$id;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get sale-payment-total for order  #'.$id;
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * get Updated Sales Credit Order
    */
    public function getUpdatedSalesCredit($day)
    {
        $days         = '-'.$day.' days';
        $fromDate      = date('Y-m-d\TG:i:s', strtotime($days));
        $toDate        = date('Y-m-d\TG:i:s', strtotime('+1 days'));
        $url         = $this->brightURL.'/order-service/order-search?orderTypeId=3&updatedOn='.$fromDate.'/'.$toDate;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Updated Sales Credit Order in '.$days.' days';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    public function orderGetUri($url, $return_type = "")
    {
          $url         = $this->brightURL.'/order-service'.$url.'?includeOptional=customFields';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get orderGetUri';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        //return $result;
 
        if ($return_type == 'object') {
            return json_decode($result);
        } else {
            return json_decode($result, true);
        }
    }
    
    
    /*
    * get Sales Order by id
    */
    public function orderById($id)
    {
          $url         = $this->brightURL.'/order-service/order/'.$id.'?includeOptional=customFields,nullCustomFields';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get order by id';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        //return $result;
        return json_decode($result, true);
    }
    

    /*
    * Post Order
    */

    public function postOrder($row)
    {
           $url         = $this->brightURL.'/order-service/order';
        $method     = 'POST';
        $data         = $row;
        $json        = true;
        $callTitle    = 'Post order row';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         return json_decode($result, true);
    }
    
    
    /*
    * Post Sales Order Item row
    */
    
    public function postOrderRow($id, $row)
    {
           $url         = $this->brightURL.'/order-service/order/'.$id.'/row';
        $method     = 'POST';
        $data         = $row;
        $json        = true;
        $callTitle    = 'Post order row';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         return json_decode($result, true);
    }
    
    /*
    * Post Sales Receipt
    */
    
    public function postSalesReceipt($row)
    {
            $url         = $this->brightURL.'/accounting-service/invoice/sales-receipt/';
        $method     = 'POST';
        $data         = $row;
        $json        = true;
        $callTitle    = 'Post Sales Receipt';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         return json_decode($result, true);
    }
    
    /*
    * Update Sales Order Status
    */
    public function updateOrderStatus($id, $data)
    {
    
           $url         = $this->brightURL.'/order-service/order/'.$id.'/status';
        $method     = 'PUT';
        $json        = true;
        $callTitle    = 'Modify Order Status';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         return json_decode($result, true);
    }
    

    /*
    * Post Order Custom Attributes
    */
    
    public function postOrderCustomAttribute($orderid, $data)
    {
           $url         = $this->brightURL.'/order-service/order/'.$orderid.'/custom-field';
        $method     = 'PATCH';
        $data         = $data;
        $json        = true;
        $callTitle    = 'Post Order Custom Attributes';
         $result     = $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         return json_decode($result, false);
    }
 

    /*
    * get All Order Status
    */
    public function getAllOrderStatus()
    {
         $url         = $this->brightURL.'/order-service/order-status/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Order Status';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
 
    /*
    * get All Payment Method
    */
    public function getAllPaymentMethod()
    {
         $url         = $this->brightURL.'/accounting-service/payment-method/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Payment Method';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * get All Shipping Method
    */
    public function getAllShippingMethod()
    {
         $url         = $this->brightURL.'/warehouse-service/shipping-method/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Shipping Method';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }


  
    /*
    * get All Price List
    */
    public function getAllPriceList()
    {
         $url         = $this->brightURL.'/product-service/price-list/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Price List ';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
 

     /*
    * get All Collections
    */
    public function getAllCollection()
    {
         $url         = $this->brightURL.'/product-service/collection-search/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Collections';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }



     /*
    * get All Collections
    */
    public function getAllSeason()
    {
         $url         = $this->brightURL.'/product-service/season/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Collections';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }



    /*
    * get All Tag
    */
    public function getAllTag()
    {
         $url         = $this->brightURL.'/contact-service/tag/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Tag';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
 
    /*
    * get All Nominals
    */
    public function getAllNominals()
    {
         $url         = $this->brightURL.'/accounting-service/nominal-code-search/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Nominals';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
 
    /*
    * get All Tax
    */
    public function getAllTax()
    {
         $url         = $this->brightURL.'/accounting-service/tax-code/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Tag';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
 
    /*
    * get All Warehouse
    */
    public function getAllWarehouse()
    {
         $url         = $this->brightURL.'/warehouse-service/warehouse/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Warehouse';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * get All Webhook
    */
    public function getAllWebhook()
    {
         $url         = $this->brightURL.'/integration-service/webhook/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Webhook';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }
    
    /*
    * Create Webhook
    */
    public function createWebhook($data)
    {
    
          $url         = $this->brightURL.'/integration-service/webhook/';
        $method     = 'POST';
        $json        = 'no_change';
        $callTitle    = 'Post Create Webhook';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         return json_decode($response, true);
    }
    
    /*
    * Delete Webhook
    */
    public function deleteWebhook($id)
    {
         $url         = $this->brightURL.'/integration-service/webhook/'.$id;
        $method     = 'DELETE';
        $data         = '';
        $json        = false;
        $callTitle    = 'Remove Webhook ';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }

    /*
    * post Customer Payment
    */
    public function postCustomerPayment($row)
    {
        $url         = $this->brightURL.'/accounting-service/customer-payment/';
        $method     = 'POST';
        $data         = $row;
        $json        = true;
        $callTitle    = 'Post Customer Payment';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return $result;
        // return json_decode($result, true);
    }
    
    /*
    * Search Purchase Order
    */

    public function searchPurchaseOrder($filter = "orderTypeId=2", $retunrType = '')
    {
        $mainarr            = [];
        $retunrTypeArray    = [];
        
        $url         = $this->brightURL.'/order-service/order-search?'.$filter;
        $method     = 'GET';
        $data         = '';
        $json        = true;
        $callTitle    = 'Search Purchase Order';
        $response     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        $results     =  json_decode($response, true);
        
		if ( array_key_exists("response", $results) )
		{
		
			$resArray    =  $results['response'];
        
			if (is_array($resArray))
			{
				if (array_key_exists("results", $resArray) && count($resArray['results']) > 0) 
				{
					$i = 0;
					$arrTxt = [];
					foreach ($resArray['results'] as $key => $value) {
						 $retunrTypeArray[] = $value[0];
						if ($i > 15) {
							$i=0;
							$arrTxt[] = $value[0];
							$mainarr[] = "/order/".implode(",", $arrTxt);
							$arrTxt = [];
						} else {
							$arrTxt[] = $value[0];
							$i++;
						}
					}
					$mainarr[] = "/order/".implode(",", $arrTxt);
				}
			}
			
		}
		
        if ($retunrType =="") {
            return $mainarr;
        } else {
            return $retunrTypeArray;
        }
    }


    /*
    * Get Goods in note Qty For Po Products
    */
    public function getGoodsinnoteQtyForPoProducts($po_id, $return_type = "")
    {
          $url         = $this->brightURL.'/warehouse-service/goods-in-search?purchaseOrderId='.$po_id;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Goods in note by PO id';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        //return $result;
        if ($return_type == 'object') {
            return json_decode($result);
        } else {
            return json_decode($result, true);
        }
    }
    
    /*
    * Get Goods out note by id
    */
    public function getGoodsOutNote($id, $return_type = "")
    {
          $url         = $this->brightURL.'/warehouse-service/order/*/goods-note/goods-out/'.$id;
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Goods out note by id';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        //return $result;
        if ($return_type == 'object') {
            return json_decode($result);
        } else {
            return json_decode($result, true);
        }
    }

     /*
    * Post Goods out note by id
    */
    public function PostGoodsOutNote($id, $data, $return_type = "")
    {
    
        $url         = $this->brightURL . '/warehouse-service/order/'.$id.'/goods-note/goods-out';
        $method     = 'POST';
        $json        = 'no_change';
        $callTitle    = 'Post Create Webhook';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return $response;
    }
        

     /*
    * Stock Transfer for inventory in transit
    */
    public function GetStockTransfer()
    {
        $url         = $this->brightURL . '/warehouse-service/stock-transfer/';
        $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get  stock-transfer';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }


     /*
    * Post Sales Credit
    */
    public function postSalesCredit($row, $return_type = "")
    {
         $url         = $this->brightURL.'/order-service/sales-credit/';
        $method     = 'POST';
        $data         = $row;
        $json        = true;
        $callTitle    = 'Post Sales Credit';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        if ($return_type == 'object') {
            return json_decode($result);
        } else {
            return json_decode($result, true);
        }
    }

     /*
    * Post Shipment Event
    */
    public function postShipmentEvent($goodnoteID, $row, $return_type = "")
    {
          $url         = $this->brightURL.'/warehouse-service/goods-note/goods-out/'.$goodnoteID.'/event';
        $method     = 'POST';
        $data         = $row;
        $json        = true;
        $callTitle    = 'Post Shipment Event';
        $result     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        if ($return_type == 'object') {
            return json_decode($result);
        } else {
            return json_decode($result, true);
        }
    }

   
     /*
    * Search  goods movement
    */
    public function searchGoodsMovement($filter, $return_type = "")
    {
        $condition = '';
        if (count($filter)>0) {
            $condition = '?'.http_build_query($filter);
        }
        
        $url         = $this->brightURL.'/warehouse-service/goods-movement-search/'.$condition;
        $method     = 'GET';
        $data         = '';
        $json        = true;
        $callTitle    = 'Search  goods movement';
        $response     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         $results     =  json_decode($response, true);
         
        $finalSearchResult = [];
        if (array_key_exists("response", $results)) {
             $response = $results['response'];
            if (array_key_exists("results", $response)) {
                 $header = [];
                foreach ($results['response']['metaData']['columns'] as $row) {
                    $header[] = $row['name'];
                }
                foreach ($results['response']['results'] as $row) {
                    $finalSearchResult[] = array_combine($header, $row);
                }
            }
        }
        return $finalSearchResult;
    }
   
   
     /*
    * Post Goods in note
    */
    public function postGoodsInNote($id, $data, $return_type = "")
    {
    
         $url         = $this->brightURL . '/warehouse-service/order/'.$id.'/goods-note/goods-in';
        $method     = 'POST';
        $json        = 'yes';
        //$callTitle    = 'Post Create Webhook';
        $callTitle    = 'Post Goods in note ';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return $response;
    }
    
    /*
    * get Warehouse default location Id
    */
    
    public function getWarehouseDefaultLocation($warehouseId)
    {
         $url         = $this->brightURL.'/warehouse-service/warehouse/'.$warehouseId.'/location/default';
         $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Warehouse default location Id';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }


    public function getAllBrightpearlProducts($type = 'products')
    {
        
         $url         = $this->brightURL.'/product-service/product';
         $method     = 'OPTIONS';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get All Brightpearl Products';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        $data = json_decode($response);
        // echo '<pre>'; print_r( $data ); echo '</pre>'; die;
        $collection = [];
        if ($data->response->getUris) {
            foreach ($data->response->getUris as $key => $value) {
                $apiUrl = $this->brightURL.'/product-service'.$value;
                if ($type == 'product-availability') {
                    $value =  str_replace("product", "product-availability", $value);
                    $apiUrl = $this->brightURL.'/warehouse-service'.$value;
                }
                $tmpMethod         = 'GET';
                $tmpData         = [];
                $tmpJson        = true;
                $tmpCallTitle    = 'Get All Brightpearl Products';
                $tmpResponse =  $this->getCommonResponse($apiUrl, $tmpMethod, $tmpData, $tmpJson, $tmpCallTitle);
                $tmpData = json_decode($tmpResponse, true);
                $collection[] = $tmpData['response'];
            }
        }
        return $collection;
    }

    

    public function getBundleAvailability($pid)
    {
           $url         = $this->brightURL.'/warehouse-service/bundle-availability/'.$pid;
         $method     = 'GET';
        $data         = [];
        $json        = true;
        $callTitle    = 'Get Bundle Availability';
        $response =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        return json_decode($response, true);
    }


    public function getProductsAvailability($range)
    {
        
         $result         = [];
        $apiUrl         = $this->brightURL.'/warehouse-service/product-availability/'.$range;
        $tmpMethod         = 'GET';
        $tmpData         = [];
        $tmpJson        = true;
        $tmpCallTitle    = 'Get All Brightpearl Products';
        $tmpResponse     =  $this->getCommonResponse($apiUrl, $tmpMethod, $tmpData, $tmpJson, $tmpCallTitle);
        $tmpData         = json_decode($tmpResponse, true);
        $result            = $tmpData['response'];
         return $result;
    }

    public function serchSalesCreditByExternalRef($externalRef)
    {
        
        $data        = [];
        $filter     = 'orderTypeId=3&externalRefSearchString='.$externalRef;
        $url         = $this->brightURL.'/order-service/order-search?'.$filter;
        $method     = 'GET';
        $data         = '';
        $json        = true;
        $callTitle    = 'Search Sales Credit Order';
        $response     =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
         $res     =  json_decode($response, true);
        
        if (array_key_exists("response", $res)) {
            $response =  $res['response'];
            
            if (array_key_exists("metaData", $response)) {
                $header = [];
                foreach ($response['metaData']['columns'] as $column) {
                     $header[] = $column['name'];
                }
            }
              
            if (array_key_exists("results", $response)) {
                $searchResults = $response['results'];
                if (count($searchResults)>0) {
                    $data = $searchResults[0];
                    if (count($header)>0) {
                        $data = array_combine($header, $data);
                    }
                }
            }
        }
        return $data;
    }
	
	public function getAllCurrency()
	{
        $url		= $this->brightURL.'/accounting-service/currency-search';
        $method		= 'GET';
        $data		= [];
        $json		= true;
        $callTitle	= 'Get Currency';
        $response	=  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
        $res 		=  json_decode($response,true);

		$header = array();
		foreach($res['response']['metaData']['columns'] as $key => $columns ){
			$header[$key] = strtolower($columns['name']);			
		}
		
		$bpCurrency = array();

		foreach($res['response']['results'] as $key => $row ){
			
			$bpCurrency[] = array_combine($header,$row);	
		}
		return $bpCurrency;
		
	}
	
	
    public function postOrderWithItems($row)
    {
		$url = $this->brightURL.'/order-service/sales-order';
		$method = 'POST';
		$data = $row;
		$json = true;
		$callTitle = 'postOrderWithItems';
		$result =  $this->getCommonResponse($url, $method, $data, $json, $callTitle);
		return json_decode($result, true);
    }
	
	public function searchSalesOrderByCustomerRef($customerRef)
	{
		$url 		= $this->brightURL.'/order-service/order-search?orderTypeId=1&customerRef='.$customerRef;
		$method     = 'GET';
		$data		= [];
		$json		= true;
		$callTitle	= 'searchSalesOrderByCustomerRef';
		$response	= $this->getCommonResponse($url, $method, $data, $json, $callTitle);
		$result =  json_decode($response, true);
		$finalReturn = '';
		if (array_key_exists('response', $result)) {
			$step1 = $result['response'];
			if (array_key_exists('results', $step1)) {
				$header = [];
				foreach($step1['metaData']['columns'] as $key=> $column) {
					$header[$key] = $column['name'];
				}

				$step2 = $step1['results'];
				if(is_array($step2)) {
					foreach($step2 as $step3) {
						$step4 = array_combine($header,$step3);
						if($step4['customerRef'] == $customerRef ) {
							$finalReturn = $step4['orderId'];
							break;
						}
					}
				}
			}
		}
		return $finalReturn;		
	}
	

	/*
	* Post Customer Custom Field Attachment 
	*/
    public function postCustomerCustomFiledsToBp($id, $data)
    {
		$url			= $this->brightURL.'/contact-service/contact/'.$id.'/custom-field';
        $method			= 'PATCH';
        $data			= $data;
        $json			= true;
        $callTitle		= 'Customer Custom Field to BP';
        return $result	=  $this->getCommonResponsePatch($url, $method, $data, $json, $callTitle);
    }
	
}
