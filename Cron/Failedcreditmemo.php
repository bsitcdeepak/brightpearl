<?php

namespace Bsitc\Brightpearl\Cron;
 
class Failedcreditmemo
{
    protected $logger;
    protected $api;
    protected $cm;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\MgtcreditmemoqueueFactory $cmqf,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger     = $loggerInterface;
        $this->api         = $brightpearlApi;
        $this->cm         = $cmqf;
        $this->log         = $log;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
             $this->cm->processFailedCreditmemo();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Process Falied Creditmemo Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
