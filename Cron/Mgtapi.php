<?php

namespace Bsitc\Brightpearl\Cron;
 
class Mgtapi
{
    protected $logger;
    protected $api;
    protected $sof;
    protected $log;
    protected $attributeapi;
    protected $brandapi;
    protected $categoryapi;
    protected $customattribute;
    protected $restapi;
  
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Bsitc\Brightpearl\Model\Api $brightpearlApi,
        \Bsitc\Brightpearl\Model\AttributeFactory $attributeapi,
        \Bsitc\Brightpearl\Model\BrandFactory $brandapi,
        \Bsitc\Brightpearl\Model\CategoryFactory $categoryapi,
        \Bsitc\Brightpearl\Model\CustomattributeFactory $customattribute,
        \Bsitc\Brightpearl\Model\BpsalesorderFactory $sof,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\LogsFactory $log
    ) {
        $this->logger             = $loggerInterface;
        $this->api                 = $brightpearlApi;
        $this->attributeapi     = $attributeapi;
        $this->categoryapi      = $categoryapi;
        $this->customattribute  = $customattribute;
        $this->brandapi         = $brandapi;
        $this->restapi          = $restapi;
        $this->sof                 = $sof;
        $this->log                 = $log;
    }
 
    public function execute()
    {
         $cron_config = $this->api->bpcron;
        if (array_key_exists("enable", $cron_config) && $cron_config['enable'] == '1') {
            $this->restapi->setAttributeOptions();
            $this->restapi->setSeasonOptions();
            $attcode = 'bp_brand';
            $this->restapi->setBrandOptions($attcode);
            $this->restapi->setCategoryApi();
            $code = 'bp_collection';
            $this->restapi->setCollectionOption($code);
        } else {
            $log = $this->log->create();
            $log->setCategory('Cron')
                   ->setTitle('MGT API Queue Cron')
                   ->setError("Disable cron in configuration")
                   ->setStoreId(1)
                   ->save();
        }
    }
}
