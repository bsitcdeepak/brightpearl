<?php

namespace Bsitc\Brightpearl\Model;

class LogsFactory
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Logs', $arguments, false);
    }
    
    public function addLog($logData)
    {
        if (!$this->getConfig('bpconfiguration/bpcron/log_recording_enable')) {
            return true;
        }
         
          $log = $this->create();
        $log->setData($logData);
           $log->save();
        return true;
    }
     
    public function recordLog($msg, $title = "NA", $category = "NA")
    {
         $logData = [];
        $logData['category']     = $category;
        $logData['title']         = $title;
        $logData['error']         = $msg;
          $this->addLog($logData);
         return true;
    }
    
    
    public function cleanall()
    {
        
        $resource     = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
        $table         = $resource->getTableName('bsitc_logs');
         $resource->getConnection()->truncateTable($table);
          return true;
    }
    
    
    
    public function clean()
    {
        
        if (!$this->getConfig('bpconfiguration/bpcron/log_cleaning_enable')) {
            return true;
        }
     
        $days = $this->getConfig('bpconfiguration/bpcron/log_cleaning_days');
         
        $finalDay = '-2 day';
        if ($days > 2) {
            $finalDay = '-'.$days.' day';
        }
        
        $dateObj             = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $currentDate         = $dateObj->gmtDate('Y-m-d');
         $updated_at         = date('Y-m-d', strtotime($finalDay, strtotime($currentDate)));
         $resource             = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
        $connection          = $resource->getConnection();
        $tableName             = $resource->getTableName('bsitc_logs');
          $whereConditions     = [ $connection->quoteInto('updated_at < ?', $updated_at) ];
        $deleteRows         = $connection->delete($tableName, $whereConditions);
         return true;
    }
    
    
    
    public function getConfig($path, $store = null)
    {
        $scopeConfig = $this->_objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
         return $scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
}
