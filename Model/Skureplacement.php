<?php
namespace Bsitc\Brightpearl\Model;

class Skureplacement extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Skureplacement');
    }
	
    public function addRecord($row)
    {
        if (count($row)>0) {
            $this->setData($row);
            $this->save();
        }
        return true;
    }
    
    public function updateRecord($id, $row)
    {
        $record =  $this->load($id);
        $record->setData($row);
        $record->setId($id);
        $record->save();
    }
    
    public function findRecord($column, $value)
    {
        $data = '';
        $collection = $this->getCollection()->addFieldToFilter($column, $value);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
        }
        return $data;
    }

    public function removeAllRecord()
    {
        $collection = $this->getCollection();
        $collection->walk('delete');
        return true;
    }
    
    public function removeRecord($id)
    {
        $record = $this->load($id);
        if ($record) {
            $record->delete();
        }
        return true;
    }
	
	public function getReplacedSku($store_id, $country_id, $sku)
	{
		$rsku = $sku;
		$collection = $this->getCollection()
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