<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allnominals implements \Magento\Framework\Option\ArrayInterface
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
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Nominals\Collection');
         $collection->addFieldToFilter('active', '1');
        $collection->setOrder('name', 'code');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                //$option[$item->getCode()] = $item->getName().' ( '.$item->getCode().' )';
                $option[$item->getCode()] = $item->getCode().' - '.$item->getName();
            }
        }
        return $option;
    }
}
