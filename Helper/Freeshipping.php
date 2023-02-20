<?php

namespace Bsitc\Brightpearl\Helper;

class Freeshipping extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_storeManager;
    protected $_logManager;
    protected $_resource;
    protected $_connection;
	
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bsitc\Brightpearl\Model\Logs $logManager,
		\Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_logManager = $logManager;
        $this->_resource = $resource;
        $this->_connection = $this->_resource->getConnection();
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
		if (!$cat) { $cat = "Global"; }
		$logArray['category'] = $cat;
		$logArray['title'] =  $title;
		$logArray['store_id'] =  0;
		$logArray['error'] =  json_encode($log_data, true);
		$this->_logManager->addLog($logArray);
		return true;
    }
	
    public function getCategoryProductIds($categoryIdsArray)
    {
        $product_id = [];
        $ccp = $this->_resource->getTableName('catalog_category_product');
        $sql = $this->_connection->select()->from(['ccp' => $ccp], ['product_id']);
        $sql->where('category_id IN (?)', $categoryIdsArray);
        $sql->columns(['product_id' => new \Zend_Db_Expr("group_concat(product_id SEPARATOR ',')")]);
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $product_id = array_unique(explode(",", $result['product_id']));
            sort($product_id);
        }
        return $product_id;
    }
	
	
	public function getQuoteProductIds($id)
    {
        $product_id = [];
        $ccp = $this->_resource->getTableName('quote_item');
        $sql = $this->_connection->select()->from(['ccp' => $ccp], ['product_id']);
        $sql->where('quote_id IN (?)', [$id]);
        $sql->columns(['product_id' => new \Zend_Db_Expr("group_concat(product_id SEPARATOR ',')")]);
        $response = $this->_connection->query($sql);
        $result = $response->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $product_id = array_unique(explode(",", $result['product_id']));
            sort($product_id);
        }
        return $product_id;
    }
	
	public function isAllowFreeShippingN($quoteId)
	{
		return true;
	}

	
	public function isAllowFreeShipping($quote)
	{
		$flag = false;
		$enable = $this->getConfig('bpconfiguration/tradefreeshipping/enable');
  		if ($enable)
		{
			$groupid = $this->getConfig('bpconfiguration/tradefreeshipping/groupid');
			$category = $this->getConfig('bpconfiguration/tradefreeshipping/category');
			
			$catsArray = explode(",",$category);
			$sampleProductIds = $this->getCategoryProductIds($catsArray);
			$groupArray = explode(",",$groupid);
			
			$customer = $quote->getCustomer();
			$customerGroup = "";
			if($customer->getId()){
				$customerGroup = $customer->getGroupId();
			}
			
			// --- check customer group ------ 
			if (in_array($customerGroup, $groupArray))  
			{
				$items = $quote->getAllItems();
				$chkAllSampleInCart = '1' ;
				foreach ( $items as $item ) {
					// --- check for sample product ------  
					if (!in_array($item->getProductId(), $sampleProductIds)  ){
						$chkAllSampleInCart = '0';
						break;
					}					
				}
				if ($chkAllSampleInCart == '1' and count($items) > 0 )
				{
					$flag = true;
				}
 			}
		}
		return $flag;
	}
	
	public function isAllowFurnitureShipping($quote)
	{
		$flag = true;
		$enable = $this->getConfig('Extrafee/furniturehandlingfee/enable');
  		if ($enable)
		{
			if($quote->getFeeType() == 'oversize_furniture') 
			{
				$address = $quote->getShippingAddress();
				if($address  and $address->getAddressType() == 'shipping' and $address->getCountryId() != 'GB')
				{
					$flag = false;		
				}
			}
 		}
		return $flag;
		
	}
	
	
}