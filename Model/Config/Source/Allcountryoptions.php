<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allcountryoptions implements \Magento\Framework\Option\ArrayInterface
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
        $collection = $obj->create('\Magento\Directory\Model\Country');
        $countryCollection = $collection->getCollection();
        foreach ($countryCollection as $country) {
            $option[$country->getCountryId()] = $country->getName();
        }
        return $option;
    }
    
    
    public function toCountryArray()
    {
        $option = [];
        $option[] = 'All Countries';
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $obj->create('\Magento\Directory\Model\Country');
        $countryCollection = $collection->getCollection();
        foreach ($countryCollection as $country) {
            $option[$country->getCountryId()] = $country->getName();
        }
        return $option;
    }
	
	
	
    public function toCountryValueArray()
    {
		$option = [];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$collection = $objectManager->create('\Magento\Directory\Model\Country');
		$countryCollection = $collection->getCollection();
        if (count($countryCollection)>0) 
		{
            foreach ($countryCollection as $country) {
					$option[] = array('value'=>$country->getCountryId(),'label'=>$country->getName());        
            }
        }
        return $option;
    }		
	
}
