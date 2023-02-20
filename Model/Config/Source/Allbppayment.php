<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allbppayment implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        $option = [];
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Bppayment\Collection');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                    $value = $item['code'];
                    $option[$value] = $item['name'];
            }
        }
        return $option;
    }
}
