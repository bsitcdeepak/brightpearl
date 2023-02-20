<?php

namespace Bsitc\Brightpearl\Cron;
 
class Paymentcapture
{
    protected $logger;
    protected $api;
    protected $pcf;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\PaymentcapturequeueFactory $pcf,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->pcf = $pcf;
        $this->log = $log;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
             $this->pcf->processQueue();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Process Paymentcapture Queue Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
