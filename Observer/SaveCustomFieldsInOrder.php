<?php

namespace Bsitc\Brightpearl\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class SaveCustomFieldsInOrder implements ObserverInterface
{

    protected $_request;

 
    
    public function __construct(RequestInterface $request, Json $serializer = null)
    {
        $this->_request = $request;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }
    

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $order->setData("warehouse_store", $quote->getWarehouseStore());
        
        /*
            ItemTypeInfo :  1 => Pre Order
            ItemTypeInfo :  2 => Made To Order
            ItemTypeInfo :  3 => Print To Order
            ItemTypeInfo :  4 => Trade Order
            ItemTypeInfo :  5 => Bespok Order
        */
            $chk_pre_order         = false;
            $chk_madeto_order     = false;
            $chk_printto_order     = false;
            $chk_trade_order     = false;
            $chk_bespoke_order     = false;
            
            
        $quoteItems = [];
        
        foreach ($quote->getAllItems() as $item) {
            $quoteItems[$item->getId()] = $item;
            
            switch ($item->getItemTypeInfo()) {
                case 1:
                    $chk_pre_order = true;
                    break;
                case 2:
                    $chk_madeto_order = true;
                    break;
                case 3:
                    $chk_printto_order = true;
                    break;
                case 4:
                    $chk_trade_order = true;
                    break;
                case 5:
                    $chk_bespoke_order = true;
                    break;
            }
        }
        
        /* ----------- Check order is trade order ---------------------- */
		if( $order->getCustomerId() )
		{
			$customer_group_id = $order->getCustomerGroupId();
			$customer_id = $order->getCustomerId();
			/*
			$objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
			$log = $objectManager->create('\Bsitc\Brightpearl\Model\LogsFactory');
			$log->recordLog($customer_group_id, "customer_group_id", "customer_group_id");
			*/
			$objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
			$customerHelper    = $objectManager->create('\Bsitc\Brightpearl\Helper\CustomerHelper');
			$isTradeEnable    = $customerHelper->getConfig('bpconfiguration/tradecustomer/enable');
			$configGroupids    = $customerHelper->getConfig('bpconfiguration/tradecustomer/groupid');
			$groupIdsArray     = explode(",", $configGroupids);
			if ($isTradeEnable and in_array($customer_group_id, $groupIdsArray)) {
				$chk_trade_order = true;
			}
		}
        /* ----------- set order status here ----------------- */ // item_type_info setItemTypeInfo
        if ($chk_bespoke_order == true) {
            $order->setItemTypeInfo('5');
        } elseif ($chk_trade_order == true) {
           // $order->setItemTypeInfo('4');
            $order->setItemTypeInfo('6');
        } elseif ($chk_pre_order == true) {
            $order->setItemTypeInfo('1');
        } elseif ($chk_madeto_order == true) {
            $order->setItemTypeInfo('2');
        } elseif ($chk_printto_order == true) {
            $order->setItemTypeInfo('3');
        } else {
            $order->setItemTypeInfo('0');
        }
        /* ----------- set order status here ----------------- */
        
        /* ----------- set order item option here ----------------- */
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $quoteItemId = $orderItem->getQuoteItemId();
            $quoteItem = $quoteItems[$quoteItemId];
            $additionalOptions = [];
            if ($additionalOption = $quoteItem->getOptionByCode('additional_options')) {
                $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
            }
            if (count($additionalOptions) > 0) {
                // Get Order Item's other options
                $options = $orderItem->getProductOptions();
                // Set additional options to Order Item
                $options['additional_options'] =  $additionalOptions;
                $orderItem->setProductOptions($options);
            }
        }
        /* ----------- set order item option here ----------------- */
        
        return $this;
    }
}
