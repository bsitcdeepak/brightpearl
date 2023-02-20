<?php

namespace Bsitc\Brightpearl\Model;

class CategoryFactory extends \Magento\Framework\Model\AbstractModel
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
    public $_category;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Category $category
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_category          = $category;
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Category', $arguments, false);
    }


    public function checkAlredyExits($id)
    {
        $catid = $id;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('category_id', $catid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }


    public function setCategoryApi()
    {
            
        if ($this->_api->authorisationToken) {
            $data = $this->_api->getProductCategory();
            $responses = $data['response'];
            $productdata = [];
            foreach ($responses as $response) {
                $id_exit = $this->checkAlredyExits($response['id']);
                if ($id_exit == 'true') {
                    continue;
                }
 
                    $productdata['category_id'] = $response['id'];
                    $productdata['parentId'] = $response['parentId'];
                    $productdata['active'] = $response['active'];

                    $productdata['name'] = $response['name'];
                    $productdata['createdOn'] = $response['createdOn'];
                    $productdata['createdById'] = $response['createdById'];
                    $productdata['updatedOn'] = $response['updatedOn'];
                    $productdata['updatedById'] = $response['updatedById'];

                if (array_key_exists("text", $response['description'])) {
                    $productdata['description'] = $response['description']['text'];
                } else {
                    $productdata['description'] = '';
                }

                    $productdata['sync'] = 0;
                    $this->addRecord($productdata);
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
