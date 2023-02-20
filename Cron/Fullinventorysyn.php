<?php

namespace Bsitc\Brightpearl\Cron;
 
class Fullinventorysyn
{
    protected $logger;
    protected $api;
    protected $fsn;
    protected $log;
	protected $mto;
	protected $po;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\FullstocksynFactory $fsn,
		\Bsitc\Brightpearl\Model\Bppurchaseorders $po,
		\Bsitc\Brightpearl\Model\Madetoorder $mto,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->fsn = $fsn;
		$this->mto = $mto;
		$this->po = $po;
        $this->log = $log;
    }
    
    public function execute()
    {

        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
            $this->fsn->synchronize();
			$this->mto->updateMtoCategoryProducts();
			$this->po->syncFromApi();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Full Stock Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
