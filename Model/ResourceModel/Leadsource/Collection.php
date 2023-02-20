<?php

namespace Bsitc\Brightpearl\Model\ResourceModel\Leadsource;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\Leadsource', 'Bsitc\Brightpearl\Model\ResourceModel\Leadsource');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
