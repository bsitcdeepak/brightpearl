<?php

namespace Bsitc\Brightpearl\Model\ResourceModel\Bpitems;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\Bpitems', 'Bsitc\Brightpearl\Model\ResourceModel\Bpitems');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
