<?php

namespace Bsitc\Brightpearl\Model;

class PricelistFactory extends \Magento\Framework\Model\AbstractModel
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
    public $_pricelist;

    public $_producturi;

    public $_bpproduct;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Pricelist $pricelist,
        \Bsitc\Brightpearl\Model\ProducturiFactory $producturi,
        \Bsitc\Brightpearl\Model\BpproductsFactory $bpproduct
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_pricelist          = $pricelist;
        $this->_producturi      = $producturi;
        $this->_bpproduct       = $bpproduct;
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
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Pricelist', $arguments, false);
    }
    
    public function checkAlredyExits($id)
    {

        $productid      = $id;
        $bpproducts  = $this->create();
        $collections = $bpproducts->getCollection()->addFieldToFilter('bp_product_id', $productid);
        $collections = $collections->getData();
        if ($collections) {
            return 'true';
        } else {
            return '';
        }
    }
    
    
    /*Insert All products Price list API data in custom table bsitc_brightpearl_pricelist*/

    public function getPricelistApi()
    {

        if ($this->_api->authorisationToken) {
            $producturl  = $this->_producturi->create();
            $producturls = $producturl->getCollection();
            $producturls = $producturls->getData();

            foreach ($producturls as $producturl) {
                    //print_r($producturl['url']);
                if ($producturl['url']) {
                    $data        = str_replace("/product/", "", $producturl['url']);
                    $datas       = explode(',', $data);
                    foreach ($datas as $data) {
                        $responses = $this->_api->getProductPriceListNew($data);
                        if ($responses) {
                               $this->setPriceData($responses);
                        }
                    }
                }
            }
        }
    }
    

    public function getPricelistByProductApi()
    {

        if ($this->_api->authorisationToken) {
            $producturl  = $this->_bpproduct->create();
            $producturls = $producturl->getCollection();
            $producturls = $producturls->getData();

            foreach ($producturls as $producturl) {
                $productid = $producturl['product_id'];
                if ($productid) {
                    $responses = $this->_api->getProductPriceListNew($productid);
                    if ($responses) {
                           $this->setPriceData($responses);
                    }
                } else {
                    return 'No products found in products tables';
                }
            }
        }
    }
    

    

    public function setPriceData($responses)
    {

         $responses = $responses;
         $responses = $responses['response'];

        foreach ($responses as $response) {
            $id_exit = $this->checkAlredyExits($response['productId']);
            if ($id_exit == 'true') {
                continue;
            }
                           $pricedata['bp_product_id'] = $response['productId'];
                           $pricedata['bp_pricelist'] = json_encode($response['priceLists']);
                           $pricedata['mg_product_id'] = '';
                           $pricedata['sync'] = 0;
                           $pricedata['queue_status'] = 'pending';
                           $this->addRecord($pricedata);
        }
    }
    
    public function addRecord($data)
    {
        $pricelistdata  = $this->create();
        $pricelistdata->setData($data);
        $pricelistdata->save();
    }
}
