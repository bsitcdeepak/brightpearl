<?php

namespace Bsitc\Brightpearl\Model;

class SwedishFactory
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Swedish', $arguments, false);
    }
	
    public function addRecord($row)
    {
        if (count($row)>0) {
            $record = $this->create();
            $record->setData($row);
            $record->save();
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
	
    public function getConfigureShippingCountryArray() 
    {
		$data = [];
        $collection = $this->create()->getCollection()->addFieldToFilter('status', 1);
		 if ($collection->getSize()) {
			foreach ($collection as $row) {
				$sc = explode(",",$row->getShippingCountry());
				foreach($sc as $c){
					$record = trim($c);
					$data[$record] = $record;
				}				
			}
		 }
		return $data;
    }
		
    public function getConfigureCategoryArray()  
    {
		$data = [];
        $collection = $this->create()->getCollection()->addFieldToFilter('status', 1);
		 if ($collection->getSize()) {
			foreach ($collection as $row) {
				$sc = explode(",", $row->getProductCategory() );
				foreach($sc as $c){
					$record = trim($c);
					$data[$record] = trim($record);
				}				
			}
		 }
		return $data;
    }
	
    public function getExcludeProductsArray()  
    {
		$data = [];
        $collection = $this->create()->getCollection()->addFieldToFilter('status', 1);
		 if ($collection->getSize()) {
			foreach ($collection as $row) {
				$ep = explode(",", $row->getExcludeProducts() );
				foreach($ep as $p){
					$record = trim($p);
					$data[$record] = trim($record);
				}				
			}
		 }
		return $data;
    }
	
	public function getSwedishVatInfo()
	{
		$recordArray = [];
 		$collection = $this->create()->getCollection()->addFieldToFilter('status', 1);
		if ($collection->getSize()) 
		{
			foreach ($collection as $row) 
			{
				$sc = explode(",", $row->getShippingCountry() );
				foreach($sc as $c){
					$record = trim($c);
					$a =  explode(",", $row->getProductCategory() );
					foreach($a as $b){
						$recordArray[$record][$b]['tax_percent'] = $row->getTaxPercent(); 
						$recordArray[$record][$b]['tax_class'] = $row->getTaxClass(); 
					}
				}
			}
		 }
		return $recordArray;
	}
	
}