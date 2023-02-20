<?php

namespace Bsitc\Brightpearl\Model;

class BpitemsFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    public $_logManager;
    public $_api;
    public $_scopeConfig;
    protected $_resource;
    protected $_connection;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\Api $api,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_scopeConfig        = $scopeConfig;
        $this->_resource        = $resource;
        $this->_connection        = $this->_resource->getConnection();
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpitems', $arguments, false);
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
    
    
    public function synBpProducts()
    {
        $bsbpi = $this->_resource->getTableName('bsitc_brightpearl_bpitems');
        $this->_connection->truncateTable($bsbpi);
         
         // $items = $this->_api->getAllBrightpearlProducts('product-availability');
        $collection = $this->_api->getAllBrightpearlProducts();
        if (count($collection) > 0) {
            foreach ($collection as $items) {
                foreach ($items as $item) {
                    $bpStatus = $item['status'];
                    if ($bpStatus == 'LIVE') {
                        $row =[];
                        $row['bp_id']         = $item['id'];
                        $row['bp_sku']         = $item['identity']['sku'];
                        $row['bp_ptype']     = ($item['composition']['bundle'] > 0 ? 1 : 0);
                        $this->addRecord($row);
                    }
                }
            }
        }
          return true;
    }
    
    
    public function productEvent($data, $type)
    {
        
        if ($type == 'created') {
            $pid = $data['id'];
            if ($pid) {
                $result = $this->_api->getProductById($pid);
                if (array_key_exists("response", $result)) {
                    $response = $result['response'][0];
                    
                    $bpStatus = $response['status'];
                    if ($bpStatus == 'LIVE') {
                         $row =[];
                        $row['bp_id']         = $response['id'];
                        $row['bp_sku']         = $response['identity']['sku'];
                        $row['bp_ptype']     = $response['composition']['bundle'];
                        $this->addRecord($row);
                    }
                }
            }
        }
        
        if ($type == 'destroyed') {
            $pid = $data['id'];
            if ($pid) {
                $search = $this->findRecord('bp_id', $pid);
                $this->_logManager->recordLog(json_encode($search->getData(), true), $type, "Product Event Search");
                if ($search) {
                    $rowId = $search->getId();
                    $this->removeRecord($rowId);
                }
            }
        }
        
        return true;
    }
}
