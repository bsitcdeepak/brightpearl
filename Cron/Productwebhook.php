<?php

namespace Bsitc\Brightpearl\Cron;
 
class Productwebhook
{
    protected $logger;
    protected $api;
    protected $log;
    protected $productwebhook;

  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\LogsFactory $log,
        \Bsitc\Brightpearl\Helper\Productupdate $productwebhook
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->log = $log;
        $this->productwebhook = $productwebhook;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
               /* Product Webhooks Cron */
               $this->productwebhook->ProductupdateSync();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('BP API Queue for product webhooks Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
