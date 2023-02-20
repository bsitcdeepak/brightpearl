<?php

namespace Bsitc\Brightpearl\Model;

class ProductinventoryFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_api;
    
    protected $_webhookinventory;
    
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\WebhookinventoryFactory $webhookinventory
    ) {
        $this->_objectManager             = $objectManager;
        $this->_api                       = $api;
        $this->_webhookinventory           = $webhookinventory;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Productinventory', $arguments, false);
    }



    public function checkAlredyExits($id)
    {
        $productid      = $id;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('product_id', $productid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }
    

    public function Productinventory()
    {
        if ($this->_api->authorisationToken) {
            $collections = $this->_webhookinventory->create()->getCollection();
            $collections = $collections->getData();
            
            $inventorydata = [];
            
            foreach ($collections as $collection) {
                if (array_key_exists("bp_id", $collection)) {
                        $productid = $collection['bp_id'];
                        $data = $this->_api->fetchProductStock($productid);
                        
                    if (array_key_exists('response', $data)) {
                        $responses = $data['response'];
                        
                        
                        foreach ($responses as $response) {
                                $inventorydata['bp_id'] = $productid;
                            if (array_key_exists('total', $response)) {
                                $inventorydata['total'] = json_encode($response['total']);
                            }
                            if (array_key_exists('warehouses', $response)) {
                                $inventorydata['warehouses'] = json_encode($response['warehouses']);
                            }
                                $inventorydata['status'] = 'pending';
                            if ($inventorydata) {
                                $this->addRecord($inventorydata);
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function addRecord($data)
    {
        $bpproducts  = $this->create();
        $bpproducts->setData($data);
        $bpproducts->save();
    }
}
