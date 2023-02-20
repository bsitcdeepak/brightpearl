<?php

namespace Bsitc\Brightpearl\Model;

class AttributeFactory extends \Magento\Framework\Model\AbstractModel
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
    public $_attribute;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Attribute $attribute
    ) {
        $this->_objectManager          = $objectManager;
        $this->_storeManager        = $storeManager;
        $this->_api                 = $api;
        $this->_logManager          = $logManager;
        $this->_date                   = $date;
        $this->_attribute              = $attribute;
        $this->_scopeConfig         = $scopeConfig;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Attribute', $arguments, false);
    }

    
    public function checkAlredyExits($id, $code)
    {
        $optionid = $id;
        $code     = $code;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()
                ->addFieldToFilter('option_value_id', $optionid)
                ->addFieldToFilter('attr_code', $code);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }


    public function setAttributeOptions()
    {
        if ($this->_api->authorisationToken) {
            $this->removeAllRecord();
            $datahelper = $this->_objectManager->create('Bsitc\Brightpearl\Helper\Data');
            $bpcolour     = trim($datahelper->getBpColour());
            $bpsize     = trim($datahelper->getBpSize());
            $mgtcolour     = trim($datahelper->getColour());
            $mgtsize     = trim($datahelper->getSize());
            $code = '';
            $alloptions = $this->_api->getProductOption();
            $this->_logManager->recordLog(json_encode($alloptions), "getProductOption", "getProductOption");
            $tempArray = [];
            $tempArray['bpcolour']         = $bpcolour;
            $tempArray['bpsize']         = $bpsize;
            $tempArray['mgtcolour']     = $mgtcolour;
            $tempArray['mgtsize']         = $mgtsize;
            $this->_logManager->recordLog(json_encode($tempArray), "tempArray", "tempArray");
            $att_option_id = [];
            foreach ($alloptions as $alloption) {
                foreach ($alloption as $option) {
                    if (($bpcolour == $option['name']) || ($bpsize == $option['name'])) {
                        if (($bpcolour == $option['name'])) {
                            $code = $mgtcolour;
                        }

                        if (($bpsize == $option['name'])) {
                            $code = $mgtsize;
                        }

                        if ((array_key_exists("id", $option))) {
                            $id = $option['id'];
                            if ($id) {
                                $data = $this->_api->getProductOptionValue($id);
                                 $responses = $data['response'];
                                $attrdata = [];
                                foreach ($responses as $response) {
                                    $id_exit = $this->checkAlredyExits($response['optionValueId'], strtolower($option['name']));
                                    if ($id_exit == 'true') {
                                        continue;
                                    }
                                    $attrdata['option_id'] = $response['optionId'];
                                    $attrdata['attr_code'] = $option['name'];
                                    $attrdata['option_value_id'] = $response['optionValueId'];
                                    $attrdata['option_value_name'] = $response['optionValueName'];
                                    $attrdata['sort_order'] = $response['sortOrder'];
                                    $attrdata['mgt_code'] = $code;
                                    $attrdata['sync'] = 0;
                                    $this->_logManager->recordLog(json_encode($attrdata), "attrdata", "attrdata");
                                    $this->addRecord($attrdata);
                                }
                            }
                        }
                    }
                }
            }
            $this->setSeasonOptions();
            $this->setBpSeasonOptions();
        }
    }


    public function setSeasonOptions()
    {
        if ($this->_api->authorisationToken) {
            $alloptions = $this->_api->getAllSeason();
            $att_option_id = [];
            foreach ($alloptions as $alloption) {
                foreach ($alloption as $option) {
                    if ((array_key_exists("id", $option))) {
                        $id = $option['id'];
                        if ($id) {
                            $id_exit = $this->checkAlredyExits($option['id'], 'season');
                            if ($id_exit == 'true') {
                                continue;
                            }
                            $attrdata['option_id'] = '';
                            $attrdata['attr_code'] = 'season';
                            $attrdata['option_value_id'] = $option['id'];
                            $attrdata['option_value_name'] = $option['name'];
                            $attrdata['sort_order'] = '';
                             $attrdata['mgt_code'] = 'season';
                            $attrdata['sync'] = 0;
                            $this->addRecord($attrdata);
                        }
                    }
                }
            }
        }
    }



    public function setBpSeasonOptions()
    {
        if ($this->_api->authorisationToken) {
            $alloptions = $this->_api->getAllSeason();
            $att_option_id = [];
            foreach ($alloptions as $alloption) {
                foreach ($alloption as $option) {
                    if ((array_key_exists("id", $option))) {
                        $id = $option['id'];
                        if ($id) {
                            $id_exit = $this->checkAlredyExits($option['id'], 'bp_season');
                            if ($id_exit == 'true') {
                                continue;
                            }
                            $attrdata['option_id'] = '';
                            $attrdata['attr_code'] = 'bp_seasons';
                            $attrdata['option_value_id'] = $option['id'];
                            $attrdata['option_value_name'] = $option['name'];
                            $attrdata['sort_order'] = '';
                            $attrdata['mgt_code'] = 'bp_seasons';
                            $attrdata['sync'] = 0;
                            $this->addRecord($attrdata);
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
    
    public function removeAllRecord()
    {
        $collection = $this->create()->getCollection();
        $collection->walk('delete');
        return true;
    }
}
