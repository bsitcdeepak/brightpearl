<?php

namespace Bsitc\Brightpearl\Cron;
 
class Synconfigurableproduct
{
    protected $logger;
    protected $api;
    protected $log;
    protected $syncconfigproducts;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\LogsFactory $log,
        \Bsitc\Brightpearl\Model\AssociateproductFactory $syncconfigproducts
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->log = $log;
        $this->syncconfigproducts = $syncconfigproducts;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
            /* Sync configurable products fetch associated configurable products in local tables */
            $this->syncconfigproducts->setSuperProductIds();
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
