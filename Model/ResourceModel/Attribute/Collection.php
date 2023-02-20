<?php

namespace Bsitc\Brightpearl\Model\ResourceModel\Attribute;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\Attribute', 'Bsitc\Brightpearl\Model\ResourceModel\Attribute');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
