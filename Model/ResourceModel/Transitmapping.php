<?php
namespace Bsitc\Brightpearl\Model\ResourceModel;

class Transitmapping extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bsitc_transittime_shipping_mapping', 'id');
    }
}
