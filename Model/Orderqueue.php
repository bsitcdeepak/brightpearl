<?php

namespace Bsitc\Brightpearl\Model;

class Orderqueue extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Orderqueue');
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
}
