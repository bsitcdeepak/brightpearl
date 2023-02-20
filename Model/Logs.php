<?php

namespace Bsitc\Brightpearl\Model;

class Logs extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Logs');
    }
    
    public function addLog($logData)
    {
          $log = $this;
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
}
