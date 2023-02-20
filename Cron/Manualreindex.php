<?php

namespace Bsitc\Brightpearl\Cron;
 
class Manualreindex
{
    protected $logger;
    protected $api;
    protected $mr;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\Manualreindex $manualreindex,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->mr = $manualreindex;
        $this->log = $log;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
             $this->mr->processReindex();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Process Manual Reindex Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
