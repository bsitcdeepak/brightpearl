<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Channel implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        
        $arr = $this->toArray();
        $ret = [];
        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }


    
    
    public function toArray()
    {
        return $this->getSatus();
    }

    
    
    public function getSatus()
    {
        $option = [];
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Channel\Collection');
        $collection->setOrder('name', 'ASC');
         //$collection->addFieldToFilter('order_type_code','PO');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $option[$item->getChannelId()] = $item->getName();
            }
        }
    
        return $option;
    }
}
