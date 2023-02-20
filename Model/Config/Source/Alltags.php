<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Alltags implements \Magento\Framework\Option\ArrayInterface
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
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Tags\Collection');
        $collection->setOrder('name', 'ASC');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $option[$item->getTagId()] = $item->getName();
            }
        }
        return $option;
    }
}
