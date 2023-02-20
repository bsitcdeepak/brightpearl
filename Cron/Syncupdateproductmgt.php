<?php

namespace Bsitc\Brightpearl\Cron;
 
class Syncupdateproductmgt
{
    protected $logger;
    protected $api;
    protected $log;
    protected $syncupdateproimg;
    protected $syncimgtobp;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\LogsFactory $log,
        \Bsitc\Brightpearl\Model\BpproductimageFactory $syncupdateproimg,
        \Bsitc\Brightpearl\Model\BpproductimageFactory $syncimgtobp
    ) {
        $this->logger = $loggerInterface;
        $this->api = $brightpearlApi;
        $this->log = $log;
        $this->syncupdateproimg = $syncupdateproimg;
        $this->syncimgtobp = $syncimgtobp;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
            /* Assign simple products to configurable products */
            $this->syncupdateproimg->SyncMgtProUpdate();
            /* Sync Images to BP from magento Images queue systems */
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
