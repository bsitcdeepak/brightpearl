<?php

namespace Bsitc\Brightpearl\Model;

class WebhookupdateFactory
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Webhookupdate', $arguments, false);
    }

    public function checkAlredyExits($id)
    {
        $brandid = $id;
        $inventory  = $this->create();
        $collections = $inventory->getCollection()->addFieldToFilter('bp_id', $brandid);
        $collections = $collections->getData();
        
        $id = '';
        foreach ($collections as $collection) {
            $id = $collection['id'];
            return $id;
        }
    }
    

    public function Webhookupdate($data)
    {
                $data = $data;
                $id = trim($data['id']);
                $webhookdata = [];
        if ($id) {
            $webhookdata['bp_id']           = $data['id'];
            $webhookdata['account_code']    = $data['accountCode'];
            $webhookdata['resource_type']   = $data['resourceType'];
            $webhookdata['lifecycle_event'] = $data['lifecycleEvent'];
            $webhookdata['full_event']      = $data['fullEvent'];
            $webhookdata['sync']            = 0;
            $webhookdata['status']          = 'pending';
            $webhookdata['created_at']      = date('Y-m-d H:i:s');
            $id_exit = $this->checkAlredyExits(trim($webhookdata['bp_id']));
            if ($id_exit == '') {
                        $this->addRecord($webhookdata);
            } else {
                        /*Update Webhook status*/
                        $this->UpdateRecord($id_exit);
            }
        }
    }
    
    
    public function addRecord($data)
    {
        $bpproducts  = $this->create();
        $bpproducts->setData($data);
        $bpproducts->save();
    }


    /*Update Records in Magento 2*/

    public function UpdateRecord($id)
    {
        //$value = date('Y-m-d H:i:s');
        $value = 'pending';
        $bpproducts  = $this->create();
        $bpproducts = $bpproducts->load($id);
        $bpproducts = $bpproducts->setData('status', $value);
        $bpproducts = $bpproducts->save();
    }
    
    public function cleanall()
    {
          $collection = $this->create()->getCollection();
         $collection->walk('delete');
         return true;
    }
}
