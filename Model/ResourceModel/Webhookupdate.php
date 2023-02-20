<?php

namespace Bsitc\Brightpearl\Model\ResourceModel;

class Webhookupdate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bsitc_brightpearl_webhooks_update', 'id');
    }
}
