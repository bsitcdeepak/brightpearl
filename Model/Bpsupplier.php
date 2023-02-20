<?php

namespace Bsitc\Brightpearl\Model;

class Bpsupplier extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Bpsupplier');
    }
    
     
    public function syncFromApi()
    {
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
         $api = $obj->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $collection = $this->getCollection();

            if (count($collection)>0) {
                $day = 1 ;
                $suppliers = $api->getUpdatedSupplier($day);
            } else {
                $suppliers = $api->getAllSupplier();
            }
            
            if (count($suppliers['response']['results']) > 0) {
                foreach ($suppliers['response']['results'] as $res) {
                    $supplierId = $res[0];
                    $result = $api->getSupplier($supplierId);
                    $supplier = $result['response'][0];
                    if (array_key_exists("contactId", $supplier)) {
                        $row = [];
                        $row['contactid']        =    @$supplier['contactId'];
                        $row['firstname']        =    @$supplier['firstName'];
                        $row['lastname']        =    @$supplier['lastName'];
                        $row['email']            =    @$supplier['communication']['emails']['PRI']['email'];
                        $row['pcf_leadtime']    =    @$supplier['customFields']['PCF_LEADTIME'];
                        $row['company']            =    @$supplier['organisation']['name'];
                        $row['pcf_leadtime']    =    @$supplier['customFields']['PCF_LEADTIME'];
                        $row['store_id']        =    '0';
                         $row['created_at']        =    date("Y-m-d h:i:s");
                        if (trim($row['pcf_leadtime']) =="") {
                            $row['pcf_leadtime'] = 0;
                        }
                         $search = $this->findSupplier('contactid', $row['contactid']);
                        if ($search!="") {
                            $this->updateSupplier($search->getId(), $row); // --- Update Supplier
                        } else {
                            $this->addSupplier($row);        //---- Add Supplier
                        }
                    }
                    // sleep(1);
                }
            }
            // Mage::getSingleton('adminhtml/session')->addSuccess('Supplier have been synchronized with brightpearl for web site ' . $website_name . '.');
        }
    }
    
    public function addSupplier($row)
    {
        if (count($row)>0) {
            $this->setData($row);
            $this->save();
        }
        return true;
    }
    
    public function updateSupplier($id, $row)
    {
        $supplier =  $this->load($id);
        $supplier->setData($row);
        $supplier->setId($id);
        $supplier->save();
        return true;
    }

    public function findSupplier($column, $value)
    {
        $data = '';
        $collection = $this->getCollection();
        $collection->addFieldToFilter($column, $value);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
        }
        return $data;
    }
    
    public function removeAllSuppliers()
    {
         $collection = $this->getCollection();
        $collection->walk('delete');
         return true;
    }
    
    public function removeSupplier($id)
    {
          $supplier = $this->load($id);
        if ($supplier) {
            $supplier->delete();
        }
        return true;
    }
}
