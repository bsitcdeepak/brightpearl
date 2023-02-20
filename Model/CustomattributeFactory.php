<?php

namespace Bsitc\Brightpearl\Model;

class CustomattributeFactory extends \Magento\Framework\Model\AbstractModel
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
    public $_brand;

    public $_customattribute;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Brand $brand,
        \Bsitc\Brightpearl\Model\Customattribute $customattribute
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_brand              = $brand;
        $this->_customattribute  = $customattribute;
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Customattribute', $arguments, false);
    }


    public function checkAlredyExits($id, $code)
    {
        $colid = $id;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('brand_id', $colid)->addFieldToFilter('code', $code);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }


    public function syncCustomattributeApi($code)
    {
          $code = $code;
          $bpcode = '';
          $mgtcode = '';

          $allcodes = explode(":", $code);
        if (array_key_exists(0, $allcodes)) {
              $bpcode = trim($allcodes[0]);
        }

        if (array_key_exists(1, $allcodes)) {
              $mgtcode = trim($allcodes[1]);
        }


        if ($this->_api->authorisationToken) {
            //$data = $this->_api->getAllCollection();
            $data  = $this->_api->getCustomFields();
            $responses = $data['response'];
                    $results = $responses;
            try {
                foreach ($results as $result) {
                    if ($result['code'] == $bpcode) {
                        if (count($result['options'])) {
                            foreach ($result['options'] as $optiondata) {
                                $id_exit = $this->checkAlredyExits($optiondata['id'], $bpcode);
                                if ($id_exit == 'true') {
                                    continue;
                                }
                                        $branddata['code'] = $bpcode;
                                        $branddata['collection_id'] = $result['id'];
                                        $branddata['collection_name'] = $result['name'];
                                        $branddata['brand_id'] = $optiondata['id'];
                                        $branddata['custom_data'] = $optiondata['value'];
                                        $branddata['mgt_code'] = $mgtcode;
                                        $branddata['sync'] = 0;
                                        $this->addRecord($branddata);
                            }
                        }
                    }
                }
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $message = $this->__('Collection data are not inserted!', $url);
                $this->_getSession()->addError($message);
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
