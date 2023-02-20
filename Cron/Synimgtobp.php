<?php

namespace Bsitc\Brightpearl\Cron;
 
class Synimgtobp
{
    protected $logger;
    protected $api;
    protected $log;
    protected $syncimgtobp;


    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\LogsFactory $log,
        \Bsitc\Brightpearl\Model\BpproductimageFactory $syncimgtobp
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->log = $log;
        $this->syncimgtobp = $syncimgtobp;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
              $this->syncimgtobp->SyncImagetoBp();
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
