<?php

namespace Bsitc\Brightpearl\Plugin;
 
class AttributeQuoteToOrderItem
{
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);
        
         $orderItem->setItemTypeInfo($item->getItemTypeInfo());
        $orderItem->setPid($item->getPid());
        $orderItem->setGeneralinfo($item->getGeneralinfo());
        $orderItem->setLeadTimeTxt($item->getLeadTimeTxt());
        $orderItem->setLeadTimeDays($item->getLeadTimeDays());
         
        $orderItem->setExpDeliveryDate($item->getExpDeliveryDate());
        $orderItem->setPoId($item->getPoId());
        
        return $orderItem;
    }
}
