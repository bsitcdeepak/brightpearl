<?php

namespace Bsitc\Brightpearl\Model;

class AssociateproductFactory extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
   // protected $_objectManager;

    public $_objectManager;
    public $_storeManager;
    public $_scopeConfig;
    public $_logManager;
    public $_api;
    public $_date;
    public $_associatedproduct;
    public $_productapi;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\BpproductsFactory $productapi,
        \Bsitc\Brightpearl\Model\Associateproduct $associatedproduct
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_productapi      = $productapi;
        $this->_associatedproduct = $associatedproduct;
        $this->_scopeConfig     = $scopeConfig;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Associateproduct', $arguments, false);
    }


    public function checkAlredyExits($id)
    {
        $brandid = $id;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('mg_child_id', $brandid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }



    public function ProductGroupids($id)
    {
            $productcollections = $this->_productapi->create();
            $productcollections = $productcollections->getCollection()->addFieldToFilter('product_group_id', $id);
            $productcollections = $productcollections->addFieldToFilter('type', 'configurable');
            $productcollections = $productcollections->getData();
            
            $confproid = '';

        foreach ($productcollections as $productcollection) {
            $confproid  = $productcollection['conf_pro_id'];
        }

            return $confproid;
    }



    public function getSuperProductId($id)
    {
            $productcollections = $this->_productapi->create();
            $productcollections = $productcollections->getCollection()->addFieldToFilter('product_id', $id);
            $productcollections = $productcollections->getData();

        foreach ($productcollections as $productcollection) {
              $id = $productcollection['conf_pro_id'];
              $pid = '';
            if ($id) {
                $pid =  $id;
            } else {
                      $pgroupid = $productcollection['product_group_id'];
                if ($pgroupid) {
                    $pid =  $this->ProductGroupids($pgroupid);
                }
            }
        }
            return $pid;
    }

    public function setSuperProductIds()
    {
            
            $results = $this->_productapi->create();
            $results = $results->getCollection();

            $asso_data = [];
        foreach ($results as $result) {
            $id_exit = $this->checkAlredyExits($result['magento_id']);
            if ($id_exit == 'true') {
                continue;
            }

                /*Get Super product id*/
            if ($result['conf_pro_id']) {
                $confid  = $result['conf_pro_id'];
            } else {
                $confid = $this->getSuperProductId($result['product_id']);
            }

                $asso_data['bp_id']       = $result['product_id'];
                $asso_data['mg_sup_id']   = $confid;
                $asso_data['mg_child_id'] = $result['magento_id'];
                $asso_data['sync']        = 0;

            if ($asso_data['mg_sup_id']) {
                $this->addRecord($asso_data);
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
