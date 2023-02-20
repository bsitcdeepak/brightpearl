<?php

namespace Bsitc\Brightpearl\Model\ResourceModel;

class Logs extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bsitc_logs', 'id');
    }
}
