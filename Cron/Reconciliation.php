<?php

namespace Bsitc\Brightpearl\Cron;
 
class Reconciliation
{
    protected $logger;
    protected $api;
    protected $rcf;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\ReconciliationFactory $rcf,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->rcf = $rcf;
        $this->log = $log;
    }
    
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
            $this->rcf->executeReport();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Stock Reconciliation Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
