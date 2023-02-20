<?php

namespace Bsitc\Brightpearl\Cron;
 
class Inventory
{
    protected $logger;
    protected $api;
    protected $stock;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Helper\Inventory $stock,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->stock = $stock;
        $this->log = $log;
    }
    
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
             $this->stock->InventorySync();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Process Inventory Queue Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
