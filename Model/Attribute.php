<?php

namespace Bsitc\Brightpearl\Model;

class Attribute extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Attribute');
    }
}
