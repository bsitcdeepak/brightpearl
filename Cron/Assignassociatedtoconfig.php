<?php

namespace Bsitc\Brightpearl\Cron;
 
class Assignassociatedtoconfig
{
    protected $logger;
    protected $api;
    protected $log;
    protected $datahelper;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\LogsFactory $log,
        \Bsitc\Brightpearl\Helper\Data $datahelper
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->log = $log;
        $this->datahelper = $datahelper;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
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
