<?php
namespace Bsitc\Brightpearl\Model\Config\Source;

class Allmgtcustomergroup implements \Magento\Framework\Option\ArrayInterface
{


    public function toOptionArray()
    {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $groupOptions = $objectManager->create('\Magento\Customer\Model\ResourceModel\Group\Collection')->toOptionArray();

        $customergroups = [];

        foreach ($groupOptions as $groupOption) {
            $value = $groupOption['value'];
            $option[$value] = $groupOption['label'];
        }
        return $option;
    }
}
