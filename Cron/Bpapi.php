<?php

namespace Bsitc\Brightpearl\Cron;
 
class Bpapi
{
    protected $logger;
    protected $api;
    protected $sof;
    protected $log;

    protected $attributeapi;
    protected $brandapi;
    protected $categoryapi;
    protected $customattribute;

  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\AttributeFactory $attributeapi,
        \Bsitc\Brightpearl\Model\BrandFactory $brandapi,
        \Bsitc\Brightpearl\Model\CategoryFactory $categoryapi,
        \Bsitc\Brightpearl\Model\CustomattributeFactory $customattribute,
        \Bsitc\Brightpearl\Model\BpsalesorderFactory $sof,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger             = $loggerInterface;
        $this->api                 = $brightpearlApi;
        $this->attributeapi     = $attributeapi;
        $this->categoryapi      = $categoryapi;
        $this->customattribute  = $customattribute;
        $this->brandapi         = $brandapi;
        $this->sof                 = $sof;
        $this->log                 = $log;
    }
 
    public function execute()
    {
        $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
               $this->attributeapi->setAttributeOptions();
            $this->attributeapi->setSeasonOptions();
            $this->brandapi->syncBrandApi();
            $this->categoryapi->setCategoryApi();
             $code = 'bp_collection';
            $this->customattribute->syncCustomattributeApi($code);
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                    ->setTitle('BP API Queue Cron')
                    ->setError("Disable cron in configuration")
                    ->setStoreId(1)
                    ->save();
        }
    }
}
