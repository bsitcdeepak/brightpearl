<?php

namespace Bsitc\Brightpearl\Model;

class BpcurrencyFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    public $_api;
    public $_log;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\Api $api,
		\Bsitc\Brightpearl\Model\Logs $log
	)
    {
        $this->_objectManager = $objectManager;
        $this->_api = $api;
        $this->_log = $log;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpcurrency', $arguments, false);
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
    
	
     
    public function syncFromApi()
    {
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($this->_api->authorisationToken)
		{
			$this->removeAllRecord();
			$bpCurrency = $this->_api->getAllCurrency();
			
			$this->_log->recordLog(json_encode($bpCurrency,true));
			
			foreach ($bpCurrency as $currency)
			{
				$row = array();
				$row['currency_id'] =  $currency['id'];
				$row['title'] =  $currency['title'];
				$row['code'] =  $currency['code'];
				$row['symbol'] =  $currency['symbol'];
				$row['exchangerate'] =  $currency['exchangerate'];
				$row['isdefault'] =  $currency['isdefault'];
				$row['exchangeratevariancenominalcode'] =  $currency['exchangeratevariancenominalcode'];
 				$this->addRecord($row);
			}
         }
	}
	
}