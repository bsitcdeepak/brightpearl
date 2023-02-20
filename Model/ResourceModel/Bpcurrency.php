<?php
namespace Bsitc\Brightpearl\Model\ResourceModel;

class Bpcurrency extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bsitc_bp_currency', 'id');
    }
}
?>