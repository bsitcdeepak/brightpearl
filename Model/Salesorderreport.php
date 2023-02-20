<?php

namespace Bsitc\Brightpearl\Model;

class Salesorderreport extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Salesorderreport');
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
        $data = [];
        $collection = $this->getCollection()->addFieldToFilter($column, $value);
        if ($collection->getSize()) {
            //$data  = $collection->getFirstItem();
            $data  = $collection->getData();
            //$data['bp_order_id']  = $collection->getBpOrderId();
            //$data['mgt_order_id']  = $collection->getMgtOrderId();
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
}
