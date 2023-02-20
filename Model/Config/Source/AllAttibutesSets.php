<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class AllAttibutesSets implements \Magento\Framework\Option\ArrayInterface
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
		$obj = \Magento\Framework\App\ObjectManager::getInstance();
		$searchCriteriaBuilder = $obj->create('\Magento\Framework\Api\SearchCriteriaBuilder');
		$attributeSetRepository = $obj->create('\Magento\Catalog\Api\AttributeSetRepositoryInterface');

		$searchCriteria = $searchCriteriaBuilder->create();
		$attributeSet = $attributeSetRepository->getList($searchCriteria);

		if ($attributeSet->getTotalCount()) 
		{
			$attributeSetList = $attributeSet;  
			foreach ($attributeSetList->getItems() as $list) 
			{
				$option[$list->getAttributeSetId()] = $list->getAttributeSetName();
			}	
		}
        return $option;
    }

	public function getCollectionAttribute()
	{
        $option = [];
        $results = $this->getSatus();
        foreach ($results as $id=>$value) {
            $option[$id] = $value;
        }
        return $option;

	}






}
