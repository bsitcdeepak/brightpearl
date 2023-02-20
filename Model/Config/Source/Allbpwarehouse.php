<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allbpwarehouse implements \Magento\Framework\Option\ArrayInterface
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
         $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Bpwarehouse\Collection');
        $collection->setOrder('name', 'ASC');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $option[$item->getWarehouseId()] = $item->getName();
            }
        }
        return $option;
    }


    public function getLocationSatus()
    {
                
        $option = [];
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $source = $objectManager->create('Magento\Inventory\Model\Source');
        $results = $source->getCollection();
        foreach ($results as $result) {
            $option[$result->getSourceCode()] = $result->getSourceCode() .' - '. $result->getName() ;
        }
        return $option;
         /*
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $associate = $resource->getTableName('cataloginventory_stock');
        $sql = $connection->select()->from(['ce' => $associate],['website_id','stock_name']);
         $collection = $connection->fetchAll($sql);
        if(count($collection)>0){
            foreach($collection as $item){
                $option[$item['website_id']] = $item['stock_name'];
             }
        }
        return $option;
        */
    }
        
    
    public function tolocationArray()
    {
        return $this->getLocationSatus();
    }
     
    
    public function toLoctionArray()
    {
        $arr = $this->tolocationArray();
        $ret = [];
        foreach ($arr as $key => $value) {
            $ret[] = [$key => $value];
        }
        return $ret;
    }



    /* Get Magento stores from collections*/



    public function getStoreSatus()
    {

        $option = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $associate = $resource->getTableName('store');
         $sql = $connection->select()->from(['ce' => $associate], ['store_id','name']);
         $collection = $connection->fetchAll($sql);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $option[$item['store_id']] = $item['name'];
            }
        }

        return $option;
    }
        
    
    public function tonewstoreArray()
    {
        return $this->getStoreSatus();
    }
     
    
    public function toStoreArray()
    {
         $option = [];
         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $associate = $resource->getTableName('store');
        $sql = $connection->select()->from(['ce' => $associate], ['store_id','name']);
         $collection = $connection->fetchAll($sql);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                    $value = $item['store_id'];
                    $option[$value] = $item['name'];
            }
        }
        return $option;
    }



    public function toMgtPos()
    {
        $option = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $moduleManager = $objectManager->get('Magento\Framework\Module\Manager');
        if (!$moduleManager->isOutputEnabled('Magestore_InventorySuccess')) {
            return $option;
        }
        
         $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $associate = $resource->getTableName('os_warehouse');

        if ($associate) {
            $sql = $connection->select()->from(['ce' => $associate], ['warehouse_id','warehouse_name']);
             $collection = $connection->fetchAll($sql);
            if (count($collection)>0) {
                foreach ($collection as $item) {
                        $value             = $item['warehouse_id'];
                        $option[$value] = $item['warehouse_name'];
                }
            }
            return $option;
        }
    }
}
