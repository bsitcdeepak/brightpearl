<?php

namespace Bsitc\Brightpearl\Cron;
 
class Stocktransfer
{
    protected $logger;
    protected $api;
    protected $stf;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\StocktransferFactory $stf,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->stf = $stf;
        $this->log = $log;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
             $this->stf->processQueue();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Stock Transfer Queue Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
