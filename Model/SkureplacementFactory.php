<?php

namespace Bsitc\Brightpearl\Model;

class SkureplacementFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Skureplacement', $arguments, false);
    }
	
	
    public function addRecord($row, $returnId = '')
    {
        if (count($row)>0) {
            $record = $this->create();
            $record->setData($row);
            $record->save();
			if($returnId){
				return $record->getId();
			}
        }
        return true;
    }
    
    public function updateRecord($id, $row)
    {
        $record =  $this->create()->load($id);
        $record->setData($row);
        $record->setId($id);
        $record->save();
    }
    
    public function findRecord($column, $value)
    {
        $data = '';
        $collection = $this->create()->getCollection()->addFieldToFilter($column, $value);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
        }
        return $data;
    }

    public function removeAllRecord()
    {
        $collection = $this->create()->getCollection();
        $collection->walk('delete');
        return true;
    }
    
    public function removeRecord($id)
    {
        $record = $this->create()->load($id);
        if ($record) {
            $record->delete();
        }
        return true;
    }
	
	public function getReplacedSku($store_id, $country_id, $sku)
	{
		$rsku = $sku;
		$collection = $this->create()->getCollection()
		->addFieldToFilter('country', $country_id)
		->addFieldToFilter('store_id', $store_id)
		->addFieldToFilter('sku', $sku);
		 if ($collection->getSize()) {
            $item  = $collection->getFirstItem();
			$rsku  = $item->getRsku(); 
        }
		return $rsku;
		
	}
	
	
	
		
}