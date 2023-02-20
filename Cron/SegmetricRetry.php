<?php
namespace Bsitc\Brightpearl\Cron;
 
class SegmetricRetry
{
    protected $logger;
    protected $api;
    protected $po;
    protected $log;
	
	public $segmetricHelper;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\BppurchaseordersFactory $po,
        \Bsitc\Brightpearl\Model\LogsFactory $log,
		\Bsitc\Brightpearl\Helper\SegmetricHelper $segmetricHelper
		
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->po = $po;
        $this->log = $log;
		$this->segmetricHelper = $segmetricHelper;
		
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') 
		{
			$this->segmetricHelper->retryPostFailedItems();
			
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Segmetric Disable cron in configuration')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
