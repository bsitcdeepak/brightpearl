<?php
namespace Bsitc\Brightpearl\Helper;

class SegmetricHelper extends \Magento\Framework\App\Helper\AbstractHelper
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
	
	public $_segmetricEnable;
	public $_segmetricCustomerEnable;
	public $_segmetricOrderEnable;
	public $_segmetricAppConnectData;
	
	public $groupRepository;	
	public $segmatricsCustomersReport;
	public $segmatricsOrdersReport;
	public $curl;
	public $order;
	public $orderDeleteEnable;
	public $_queuestatus;
		
	public $pendingState;
    public $processingState;
    public $completeState;
    public $errorState;
    public $errorFailed;
    public $errorNotPaid;
    public $errorInvoiceAuthorized;
	
	public $productRepository;
	public $categoryRepository;
	
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
        \Magento\Newsletter\Model\Subscriber $subscriber,
		\Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
		\Bsitc\Brightpearl\Model\SegmatricscustomersFactory $segmatricscustomersFactory,
		\Bsitc\Brightpearl\Model\SegmatricsordersFactory $segmatricsordersFactory,
		\Magento\Sales\Model\Order $order,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Catalog\Model\CategoryRepository $categoryRepository		
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
		$this->_segmetricEnable = $this->segmetricStatus();
		$this->_segmetricCustomerEnable = $this->isSegmetricCustomerPostEnable();
		$this->_segmetricOrderEnable = $this->isSegmetricOrderPostEnable();
		$this->groupRepository = $groupRepository;
		$this->segmatricsCustomersReport = $segmatricscustomersFactory;		
		$this->segmatricsOrdersReport = $segmatricsordersFactory;
		$this->curl = $curl;
		$this->order = $order;
		$this->orderDeleteEnable = $this->isSegmetricDeleteCancelOrderEnable();
		
		$this->productRepository = $productRepository;
		$this->categoryRepository = $categoryRepository;
		
		$this->_queuestatus         = $queuestatus;
		$queue_status                = $this->_queuestatus->getQueueOptionArray();
		
        $this->pendingState          = $queue_status['Pending'];
        $this->processingState        = $queue_status['Processing'];
        $this->completeState        = $queue_status['Completed'];
        $this->errorState            = $queue_status['Error'];
        $this->errorFailed            = $queue_status['Failed'];
        $this->errorNotPaid            = $queue_status['Not_Paid'];
        $this->errorInvoiceAuthorized  = $queue_status['Invoice Authorized Only'];
		
        parent::__construct($context);
    }

   
    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
	
	public function postSegmetricData($postEndpoint, $data)
    {
		$jsonPostData = json_encode($data);
		$appData = $this->_segmetricAppConnectData;
		$url = "https://import.segmetrics.io/api/v1/".$appData['accountid'].'/'.$appData['integrationid'].'/'.$postEndpoint;
		$headers = ["Content-Type"=>"application/json","Authorization"=>$appData['apikey']];
		$this->curl->setHeaders($headers);
		$this->curl->post($url,$jsonPostData);
		return $this->curl->getBody();
	}
	
	public function segmetricStatus($store = null)
    {	
		$appConnectData = array();
        $statusSegmetric = $this->_scopeConfig->getValue('bpconfiguration/segmetrics/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
		
		$integrationIdSegmetric = $this->_scopeConfig->getValue('bpconfiguration/segmetrics/integration_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
		
		$apiKeySegmetric = $this->_scopeConfig->getValue('bpconfiguration/segmetrics/api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
		
		$accountIdSegmetric = $this->_scopeConfig->getValue('bpconfiguration/segmetrics/account_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
		
		if($statusSegmetric && $integrationIdSegmetric && $apiKeySegmetric && $accountIdSegmetric)
		{			
			$appConnectData['integrationid'] = $integrationIdSegmetric;
			$appConnectData['apikey'] = $apiKeySegmetric;
			$appConnectData['accountid'] = $accountIdSegmetric;			
			$this->_segmetricAppConnectData = $appConnectData;			
			return true;
		}		
		$this->_segmetricAppConnectData = $appConnectData;		
		return false;
    }
	
	
	public function isSegmetricCustomerPostEnable($store = null)
    {	
		$customerPost = $this->_scopeConfig->getValue('bpconfiguration/segmetrics/post_customers', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);	   
	    return $customerPost;
    }
	
	public function isSegmetricOrderPostEnable($store = null)
    {
		$orderPost = $this->_scopeConfig->getValue('bpconfiguration/segmetrics/post_orders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
	    return $orderPost;
    }  

	public function isSegmetricDeleteCancelOrderEnable($store = null)
    {
		$orderDelete = $this->_scopeConfig->getValue('bpconfiguration/segmetrics/delete_orders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
	    return $orderDelete;
    }
    
    public function postCustomerToSegmetric($customerId)
	{
		if($this->_segmetricEnable && $this->_segmetricCustomerEnable)
		{	
			try{
				$customerData = $this->prepareCustomerData($customerId);
				if(count($customerData))
				{
					$responseData = $this->postSegmetricData('contact', $customerData);				
					$responseDataArr = json_decode($responseData, true);
					if(isset($responseDataArr['status']) && $responseDataArr['status'] == "success")
					{
						$status = 3;
					}else{
						$status = 6;
					}
					$data = array();				
					$data['first_name'] = $customerData['first_name'];
					$data['last_name'] = $customerData['last_name'];
					$data['email'] = $customerData['email'];
					$data['status'] = $status;
					$data['json'] = $responseData;
					
					$customerReport = $this->segmatricsCustomersReport->findRecord('email', $customerData['email']);
					if($customerReport != "")
					{					
						$data['updated_at'] = date('d-m-y h:i:s');
						$this->segmatricsCustomersReport->updateRecord($customerReport->getId(), $data);
					}else{
						$data['created_at'] = date('d-m-y h:i:s');					
						$this->segmatricsCustomersReport->addRecord($data);
					}
					
					$this->_logManager->recordLog('Segmetric Post Customer Data #'.$customerId, "Segmetric Customer Id ".$customerId, "Segmetric Post Customer Success");
				}
				
			}catch(\Exception $e){					
					 $this->_logManager->recordLog('Segmetric Post Customer Data Error #'.$customerId, "Segmetric Customer Id ".$customerId, "Segmetric Post Customer Error ".$e->getMessage());
			}
		}
	}
	
	public function prepareCustomerData($customerId)
	{		
		 $mgtCustomer = $this->_customerFactory->create()->load($customerId);
		 if($mgtCustomer->getId())
		 {			
			 $group = $this->groupRepository->getById($mgtCustomer->getGroupId());
			 $data = array();
			 $data['contact_id'] = $mgtCustomer->getId();
			 $data['first_name'] = $mgtCustomer->getFirstname();
			 $data['last_name'] = $mgtCustomer->getLastname();
			 $data['email'] = $mgtCustomer->getEmail();			 
			 $data['group_id'] = $group->getCode();
			 return $data;		 
		 }		 
		 return false;		
	}
	
	public function postOrderToSegmetric($orderId)
	{
		if($this->_segmetricEnable && $this->_segmetricOrderEnable)
		{
			try{
				$dataOrder = $this->prepareOrderData($orderId);				
				if(count($dataOrder))
				{				
					$responseData = $this->postSegmetricData('invoice', $dataOrder);				
					$responseDataArr = json_decode($responseData, true);						
					if(isset($responseDataArr['status']))
					{	
						if($responseDataArr['status'] == "success")
						{
							$status = 3;
						}else{
							$status = 6;
						}					
					}					
					$data = array();				
					$data['order_id'] = $dataOrder['mgt_id'];
					$data['increment_id'] = $dataOrder['id'];				
					$data['status'] = $status;
					$data['json'] = $responseData;
					
					$orderReport = $this->segmatricsOrdersReport->findRecord('order_id', $orderId);
					if($orderReport != "")
					{					
						$data['updated_at'] = date('d-m-y h:i:s');
						$this->segmatricsOrdersReport->updateRecord($orderReport->getId(), $data);
					}else{
						$data['created_at'] = date('d-m-y h:i:s');					
						$this->segmatricsOrdersReport->addRecord($data);
					}
					$this->_logManager->recordLog('Segmetric Post Order Data #'.$orderId, "Segmetric Order Id ".$orderId, "Segmetric Post Order ");
				}
			}catch(\Exception $e){
				 $this->_logManager->recordLog('Segmetric Post Order Data Error #'.$orderId, "Segmetric Order Id ".$orderId, "Segmetric Post Order Error ".$e->getMessage());
			}
		}		
	}
	
	public function prepareOrderData($orderId)
	{
		$order = $this->order->load($orderId);
		$orderIncId = $order->getIncrementId();
		$store_id = $order->getStore()->getId();
		
		$data = array();		
		$data['id'] = $orderIncId;
		$data['mgt_id'] = $order->getId();
		$data['email'] = $order->getCustomerEmail();		
		$data['amount'] = $this->convertPriceToCent($order->getGrandTotal());
		$data['paid'] = $this->convertPriceToCent($order->getGrandTotal());
		if($order->getTotalRefunded())
		{
			$data['is_refunded'] = 1;	
		}else{				
			$data['is_refunded'] = 0;	
		}
		
		$data['date_created'] = $order->getCreatedAt();
		$data['order_currency'] = $order->getOrderCurrencyCode();		
		foreach($order->getAllVisibleItems() as $_item) 
		{
			 if($_item->getParentItem())
			 {				 
				 $item_sku = $_item->getParentItem()->getSku();
			 }else{
				 $item_sku = $_item->getSku();
			 }
			$product = $this->productRepository->get($item_sku);			
			$category = "";
			$category = $this->getProductCategory($product, $store_id);
			
			$requAttr = $this->getProductAttributeData($product);
			
			$dataArr = array('name' => $_item->getName(),
							 'product_id'=> $_item->getSku(),
							 'amount'=> $this->convertPriceToCent($_item->getRowTotalInclTax()),
							 'total_paid'=> $this->convertPriceToCent($_item->getRowTotalInclTax()),
							 'category'=>$category
							);
			$fullArr = array_merge($dataArr,$requAttr);									
			$data['items'][] = $fullArr;
		}		
		// $data['shipping'] = $this->convertPriceToCent($order->getShippingAmount()) +  $this->convertPriceToCent($order->getShippingTaxAmount());		
		//$data['discount'] = $this->convertPriceToCent($order->getDiscountAmount());		
		//$data['tax'] = $this->convertPriceToCent($order->getTaxAmount()); 		
		return $data;		
	}
	
	
	public function postCancelOrder($orderId)
	{
		if($this->_segmetricEnable && $this->orderDeleteEnable)
		{
			try{
 				$messsage = "";
				$dataOrder = $this->prepareOrderData($orderId);
				$invoiceId = $dataOrder['id'];
				$appData = $this->_segmetricAppConnectData;
				$url = "https://import.segmetrics.io/api/v1/".$appData['accountid'].'/'.$appData['integrationid'].'/invoice'.'/'.$invoiceId;
				$headers =	['Authorization: '.$appData['apikey']];
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				$responseDecode = json_decode($response, true);
				curl_close($ch);
				
				if(json_last_error() === JSON_ERROR_NONE)
				{				
					if($responseDecode['status'] == "success")
					{
						$data = array();				
						$data['order_id'] = $dataOrder['mgt_id'];
						$data['increment_id'] = $dataOrder['id'];				
						$data['status'] = 3;
						$data['json'] = $response;
						$data['created_at'] = date('d-m-y h:i:s');
						$this->segmatricsOrdersReport->addRecord($data);
						$messsage = $invoiceId.' Invoice deleted from segemtric';
					}					
				}else{	
						$messsage = $invoiceId.' Invoice not found on segemtric to delete';
				}				
				$this->_logManager->recordLog('Segmetric Delete Cancel Order Data  #'.$orderId, "Segmetric Order Id ".$orderId, $messsage);
				
			}catch(\Exception $e){
				$this->_logManager->recordLog('Segmetric Cancel Order Data Error #'.$orderId, "Segmetric Order Id ".$orderId, "Segmetric Cancel Order Error ".$e->getMessage());
			}			
		}			
	}
	
	public function convertPriceToCent($price)
	{
		return $price * 100;		
	}
	
	public function getProductCategory($product, $store_id)
	{
		 if(count($product->getCategoryIds()))
		  {
			  foreach($product->getCategoryIds() as $item)
			  {
				  $catob = $this->categoryRepository->get($item, $store_id);
				  return $catob->getName();
				  break;
			  }
		  }
		  return "";
	}
	
	public function getProductAttributeData($product)
	{
	   $requAttr = array(
						"colour"=>"",							
						"collection"=>"",							
						"style"=>""
						);	
						
	  foreach($requAttr as $attrId => $attrVal)
	  {		
		   $attribute = $product->getResource()->getAttribute($attrId);
		   if ($attribute)
			{
				 if( $val = $product->getResource()->getAttribute($attrId)->getFrontend()->getValue($product))
				 {
					 $requAttr[$attrId] = $val;
				 }
			}			 
	  }
		  
		 return $requAttr;
		 
	}


	public function	retryPostFailedItems()
	{
       
	   if($this->_segmetricEnable && $this->_segmetricOrderEnable)
	   {
		   $orderReport = $this->segmatricsOrdersReport->create()->getCollection()
							->addFieldToFilter('status', ['neq'=>$this->completeState]);
		   if(count($orderReport->getData()))
		   {
			   foreach($orderReport as $item)
			   {	
					$this->postOrderToSegmetric($item->getOrderId());
			   }
		   }
	   }
	   
	  if($this->_segmetricEnable && $this->_segmetricCustomerEnable)
	  {	 
			$customerReport = $this->segmatricsCustomersReport->create()->getCollection()
									->addFieldToFilter('status', ['neq'=>$this->completeState]);
							   
		   if(count($customerReport->getData()))
		   {
			   foreach($customerReport as $item)
			   {
				  $jsonData = json_decode($item->getJson(), true);	   
				   
				  if(isset($jsonData['contact']['contact_id']))
				   {
					  $id = $jsonData['contact']['contact_id'];
					  $this->postCustomerToSegmetric($id);
				   } 
			   }
		   }
		}		
				
	}
 
}
