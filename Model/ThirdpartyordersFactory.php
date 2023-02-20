<?php

namespace Bsitc\Brightpearl\Model;

class ThirdpartyordersFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public $_logManager;
    
    public $_bpPo;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Bsitc\Brightpearl\Model\Bppurchaseorders $bpPo
    ) {
        $this->_objectManager = $objectManager;
        $this->_logManager = $logManager;
        $this->_bpPo = $bpPo;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Thirdpartyorders', $arguments, false);
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
    
    
    public function saveThirdpartyorders($tporders)
    {
        foreach ($tporders['orderporelation'] as $tporder) {
            foreach ($tporder['Items'] as $item) {
                $row = [];
                $row['tp_order_id']    =    $tporder['mgt_order_id'];
                $row['bp_order_id']    =    $tporder['bp_order_id'];
                $row['po_id']        =    $item['po_id'];
                $row['sku']            =    trim($item['sku']);
                $row['qty']            =    (int)$item['qty'];
                
                if (in_array('', $row, true)) {
                    $msg = json_encode($row, true);
                    $this->_logManager->recordLog($msg, 'Thirdparty Order invalid item', 'Thirdparty Order');
                } else {
                    $this->addRecord($row);
                    $this->_bpPo->updateRemainder($row['po_id'], $row['sku'], $row['qty']);
                }
            }
        }
    }
}
