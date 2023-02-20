<?php

namespace Bsitc\Brightpearl\Model;

class Bporderporelation extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Bporderporelation');
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
      
    public function getPreOrderItems($_orderId)
    {
        $pre_order_item = [];
        $collection = $this->getCollection()->addFieldToFilter('order_id', $_orderId);
        $pre_order_item = $collection->getColumnValues('sku');
        return $pre_order_item;
    }
            
    public function updateOrderPoRelationColumn($column, $value, $condition)
    {
        
        if (is_array($condition) and count($condition) > 0) {
            $collection = $this->getCollection();
            foreach ($condition as $key => $item) {
                $collection->addFieldToFilter($key, $item);
            }
            if ($collection->getSize()) {
                $opr =  $collection->getFirstItem();
                if ($column and $opr) {
                    $opr->setData($column, $value);
                    $opr->save();
                }
            }
        }
        return true;
    }
}
