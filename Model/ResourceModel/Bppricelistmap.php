<?php

namespace Bsitc\Brightpearl\Model\ResourceModel;

class Bppricelistmap extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bsitc_brightpearl_map_pricelist', 'id');
    }
}
