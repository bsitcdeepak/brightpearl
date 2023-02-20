<?php

/**
 * Brightpearl API
 *
 * Brightpearl API class
 * @package brightpearl
 * @version 2.0
 * @author Vijay Vishvkarma
 * @email vijay1982.msc@gmail.com
 */

namespace Bsitc\Brightpearl\Model;

class ApiFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Api', $arguments, false);
    }
}
