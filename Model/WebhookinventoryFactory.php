<?php

namespace Bsitc\Brightpearl\Model;

class WebhookinventoryFactory
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Webhookinventory', $arguments, false);
    }

    public function checkAlredyExits($bp_id)
    {
        $collection = $this->create()->getCollection();
        $collection ->addFieldToFilter('bp_id', $bp_id);
        $collection->addFieldToFilter('status', 'pending');
        //$collection->addFieldToFilter('status', array('in' => array('pending')));
        if ($collection->getSize()) {
            return true;
        }
        return false;
    }


    public function checkWithErrorAlredyExits($bp_id)
    {
        $collections = $this->create()->getCollection()->addFieldToFilter('bp_id', $bp_id)->addFieldToFilter('status', 'error');
        $collections = $collections->getData();
        if ($collections) {
            return true;
        } else {
            return false;
        }
    }
    

    public function Webhookinventory($data)
    {
        $id = trim($data['id']);
        if ($id) {
            $webhookdata = [];
            $webhookdata['bp_id']             = $data['id'];
            $webhookdata['account_code']    = $data['accountCode'];
            $webhookdata['resource_type']     = $data['resourceType'];
            $webhookdata['lifecycle_event'] = $data['lifecycleEvent'];
            $webhookdata['full_event']        = $data['fullEvent'];
            $webhookdata['sync']             = 0;
            $webhookdata['status']             = 'pending';
            $webhookdata['created_at']         = date('Y-m-d H:i:s');
            
            if (!$this->checkAlredyExits(trim($webhookdata['bp_id']))) {
                $this->addRecord($webhookdata);
            }
        }
    }
    
    public function addRecord($data)
    {
        $bpproducts  = $this->create();
        $bpproducts->setData($data);
        $bpproducts->save();
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
    
    
    
    public function updateStuckQueueRecord()
    {
            $adminHours = 0;
        if (!$adminHours) {
            $adminHours = 2;
        }
          $collection = $this->create()->getCollection();
          $collection->addFieldToFilter('status', ['eq'=>'processing']);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $date_a = $this->_date->date($item->getUpdatedAt());
                $date_b = $this->_date->date();
                $diff = $date_a->diff($date_b)->format('%i');
                if ($diff >= $adminHours) {
                    $item->setState('pending')->save();
                }
            }
        }
          return true;
    }

    public function checkQueueProcessingStatus()
    {
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', [ 'eq'=>'processing']);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function cleanProcesQueue()
    {
        
        $collection = $this->create()->getCollection();
        $collection->addFieldToFilter('status', ['in' => ['complete']]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $item->delete();
            }
        }
    }
}
