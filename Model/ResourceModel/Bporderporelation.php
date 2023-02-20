<?php

namespace Bsitc\Brightpearl\Model\ResourceModel;

class Bporderporelation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bsitc_brightpearl_orderporelation', 'id');
    }
}
