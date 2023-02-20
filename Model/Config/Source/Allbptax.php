<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allbptax implements \Magento\Framework\Option\ArrayInterface
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
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Bptax\Collection');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $option[$item['code']] = $item['code'];
            }
        }
        return $option;
    }
	
	
    public function toBpTaxArray()
    {
    
        $option = [];
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Bptax\Collection');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $option[$item['code']] = $item['code'];
            }
        }
        return $option;
    }
	
	
	
	
}
