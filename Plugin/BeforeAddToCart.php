<?php

namespace Bsitc\Brightpearl\Plugin;

use Magento\Checkout\Model\Cart;
 
class BeforeAddToCart
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Bsitc\Brightpearl\Model\Logs $logManager,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Bsitc\Brightpearl\Model\Madetoorder $mto
		
    ) {
        $this->_logManager = $logManager;
        $this->_mto = $mto;
        $this->_checkoutSession = $checkoutSession;
		$this->productFactory = $productFactory;
    }

	public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
		
		//$this->recordLog($productInfo->getData(), 'productInfo');
		//$this->recordLog($requestInfo, 'requestInfo');
		if ($productInfo->getTypeId() == 'configurable') 
		{
			$childProduct = $requestInfo['selected_configurable_option'];
			$child = $this->productFactory->create()->load($childProduct);
			if ($child->getWkPreorder()) 
			{
				$chkItem  = $this->_mto->getPreOrderMsg($child, 'array');
				if (count($chkItem) > 0) 
				{
					$requestQty = 0;
					if (array_key_exists("qty", $requestInfo)) 
					{					
						$requestQty = $requestInfo['qty'];
						$cartQty = $this->getAlreadyAddedQty($requestInfo['product']);
						$remainder_quantity = $chkItem['remainder_quantity'] - $cartQty ;
						if($requestQty > $remainder_quantity)
						{
							$msg = __("Request quantity is not available. You can order up to %1 quantity",$remainder_quantity);
							$this->recordLog($msg, 'msg');
							throw new \Magento\Framework\Exception\LocalizedException($msg);
						}
					}
				}
			}			
		}
		
		if ($productInfo->getTypeId() == 'simple') 
		{
			if ($productInfo->getWkPreorder()) 
			{
				$chkItem  = $this->_mto->getPreOrderMsg($productInfo, 'array');
				if (count($chkItem) > 0) 
				{
					$requestQty = 0;
					if (array_key_exists("qty", $requestInfo)) 
					{
						$requestQty = $requestInfo['qty'];
 						$cartQty = $this->getAlreadyAddedQty($requestInfo['product']);
						$remainder_quantity = $chkItem['remainder_quantity'] - $cartQty ;
						if($requestQty > $remainder_quantity)
						{
							$msg = __("Request quantity is not available. You can order up to %1 quantity",$remainder_quantity);
							$this->recordLog($msg, 'msg');
							throw new \Magento\Framework\Exception\LocalizedException($msg);
						}
					}
				}
			}
		}
		
		/*
		if ($productInfo->getTypeId() == 'bundle') 
		{
			$requiredChildrenIds = $productInfo->getTypeInstance()->getChildrenIds($productInfo->getId(), true);
			foreach ($requiredChildrenIds as $childrenKey => $childrenValue) 
			{
				foreach ($childrenValue as $key => $childProductId) 
				{
					$child = $this->productFactory->load($childProductId);
					if ($child->getWkPreorder()) 
					{

					}
				}
			}			
		}
		*/
		
		
		return [$productInfo, $requestInfo];
    }

	public function getAlreadyAddedQty($pid)
	{
		$qty = 0;
		$quote = $this->_checkoutSession->create()->getQuote();
        $itemsCount = $quote->getItemsSummaryQty();
        if($itemsCount > 0 )
        {	
			foreach ($quote->getAllItems() as $item) 
			{
				/*
					ItemTypeInfo :  1 => Pre Order
					ItemTypeInfo :  2 => Made To Order
					ItemTypeInfo :  3 => Print To Order
					ItemTypeInfo :  4 => Trade Order
					ItemTypeInfo :  5 => Bespok Order
				*/
				$productId = $item->getProduct()->getId();
 				if($item->getItemTypeInfo() == "1" and $pid == $productId )
				{
					$qty = $qty + $item->getQty();
				}
			}		
		}
		return $qty ;		
	}

    public function recordLog($log_data, $title='NA')
    {
        $logArray = [];
		$logArray['category'] = 'beforeAddProduct';
		$logArray['title'] =  $title;
		$logArray['store_id'] =  0;
		$logArray['error'] =  json_encode($log_data, true);
		$this->_logManager->addLog($logArray);
		return true;
    }

}