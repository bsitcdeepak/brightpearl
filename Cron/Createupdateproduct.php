<?php

namespace Bsitc\Brightpearl\Cron;
 
class Createupdateproduct
{
    protected $logger;
    protected $api;
    protected $log;
    protected $createproduct;
    protected $syncconfigproducts;
    protected $datahelper;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\LogsFactory $log,
        \Bsitc\Brightpearl\Model\RestApi $createproduct,
        \Bsitc\Brightpearl\Model\AssociateproductFactory $syncconfigproducts,
        \Bsitc\Brightpearl\Helper\Data $datahelper
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->log = $log;
        $this->createproduct = $createproduct;
        $this->syncconfigproducts = $syncconfigproducts;
        $this->datahelper = $datahelper;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
            /* --- Create and update products in magento using crons --- */
            $this->createproduct->setProductsApi();
            /* --- Sync configurable products fetch associated configurable products in local tables --- */
            $this->syncconfigproducts->setSuperProductIds();
            /* --- Assign simple products to configurable products --- */
            $this->datahelper->setAssociateProducts();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Mgt create and update products in magento')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
