<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allbpleadsource implements \Magento\Framework\Option\ArrayInterface
{
 
    
    public function toOptionArray()
    {
    
        $option = [];
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Leadsource\Collection');
        $collection = $collection->getData();

            $option = [];
        foreach ($collection as $item) {
                $value = $item['bp_id'];
                $option[$value] = $item['name'];
        }

        return $option;
    }
}
