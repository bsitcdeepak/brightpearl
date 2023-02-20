<?php

namespace Bsitc\Brightpearl\Model\ResourceModel\Bporderporelation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\Bporderporelation', 'Bsitc\Brightpearl\Model\ResourceModel\Bporderporelation');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
