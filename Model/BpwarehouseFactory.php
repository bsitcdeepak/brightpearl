<?php
namespace Bsitc\Brightpearl\Model;

class BpwarehouseFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public $_storeManager;
    public $_scopeConfig;
    public $_logManager;
    public $_api;
    public $_date;
    public $_pricelist;
    public $_pricelistconfig;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Pricelistconfig $pricelistconfig,
        \Bsitc\Brightpearl\Model\Pricelist $pricelist
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_pricelist          = $pricelist;
        $this->_pricelistconfig = $pricelistconfig;
        $this->_scopeConfig     = $scopeConfig;
    }
    

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpwarehouse', $arguments, false);
    }
    
    
    public function checkAlredyExits($id)
    {
        $productid      = $id;
        $_bpwarehouse  = $this->create();
        $collections = $_bpwarehouse->getCollection()->addFieldToFilter('warehouse_id', $productid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }
    
    
    /*Insert Price list API data in custom table bsitc_brightpearl_pricelist*/
    public function setBpwarehouse()
    {
        if ($this->_api->authorisationToken) {
            $data = $this->_api->getAllWarehouse();
            $responses = $data['response'];
            
            $this->removeAllRecord();
             
            foreach ($responses as $response) {
                $row = [];
                 $row['warehouse_id']                 = $response['id'];
                $row['name']                         =  $response['name'];
                $row['type_code']                     = $response['typeCode'];
                $row['type_description']             = $response['typeDescription'];
                $row['address']                     = json_encode($response['address']);
                $row['click_and_collect_enabled']     = $response['clickAndCollectEnabled'];
                $row['sync']                         = 0;
                 $this->addRecord($row);
            }
        }
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
}
