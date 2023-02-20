<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allpricelist implements \Magento\Framework\Option\ArrayInterface
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
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Pricelistconfig\Collection');
        $collection->setOrder('code', 'ASC');
        if (count($collection)>0) {
            foreach ($collection as $item) {
				$tmp = json_decode($item->getName(),true);
				$tmpName = $tmp['text'];
                $option[$item->getBpId()] = $tmpName;
                // $option[$item->getBpId()] = $item->getCode();
            }
        }
        return $option;
    }
}
 
