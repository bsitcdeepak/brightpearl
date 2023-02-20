<?php

namespace Bsitc\Brightpearl\Cron;
 
class Builderstock
{
    protected $logger;
    protected $api;
    protected $builderstock;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\BuilderstockFactory $builderstock,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->builderstock = $builderstock;
        $this->log = $log;
    }
    
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
             $this->builderstock->synchronize();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Process Builder Stock Syn Job')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
