<?php

namespace Bsitc\Brightpearl\Cron;
 
class Updatemtoproducts
{
    protected $logger;
    protected $api;
    protected $mto;
    protected $log;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\Madetoorder $mto,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->mto = $mto;
        $this->log = $log;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
             $this->mto->updateMtoCategoryProducts();
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('Update Made to Order category Products')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
