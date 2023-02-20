<?php

namespace Bsitc\Brightpearl\Cron;
 
class Fulfilment
{
    protected $logger;
    protected $api;
    protected $sof;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\FulfilmentFactory $soff,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->sof = $soff;
        $this->log = $log;
    }
 
    public function execute()
    {

        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
             $this->sof->processQueue();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Process Fulfilment Queue Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
