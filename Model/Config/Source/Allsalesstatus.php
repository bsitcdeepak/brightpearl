<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allsalesstatus implements \Magento\Framework\Option\ArrayInterface
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
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Bporderstatus\Collection');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $option[$item->getStatusId()] = $item->getName();
            }
        }
        return $option;
    }
}
