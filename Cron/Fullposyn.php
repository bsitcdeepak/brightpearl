<?php

namespace Bsitc\Brightpearl\Cron;
 
class Fullposyn
{
    protected $logger;
    protected $api;
    protected $po;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\BppurchaseordersFactory $po,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->po = $po;
        $this->log = $log;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') 
		{
			$enable_fullposyn = $cron_config['enable_fullposyn'];
			if($enable_fullposyn){
				$this->po->fullSyncFromApi();
			}else{
				$log = $this->log->create();
				$log->setCategory('Cron')
						->setTitle('Disable Full Synchronize Purchase Order')
						->setError("Disable Full Synchronize Purchase Order")
						->setStoreId(1)
						->save();
			}
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Disable cron in configuration')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
