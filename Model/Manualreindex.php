<?php

namespace Bsitc\Brightpearl\Model;

class Manualreindex extends \Magento\Framework\Model\AbstractModel
{
    public $_scopeConfig;
    public $_storeManager;
    public $_objectManager;
    public $_logManager; 
	protected $indexFactory;
	protected $indexCollection;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Logs $logManager,
		\Magento\Indexer\Model\IndexerFactory $indexFactory,
		\Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection		
    ) {
        $this->_objectManager	= $objectManager;
        $this->_storeManager	= $storeManager;
        $this->_scopeConfig		= $scopeConfig;
        $this->_logManager		= $logManager;
		$this->indexFactory 	= $indexFactory;
        $this->indexCollection 	= $indexCollection;
     }

	public function processReindex()
	{
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable_manualreindex = $this->_scopeConfig->getValue('bpconfiguration/bpcron/enable_manualreindex', $storeScope);
		if($enable_manualreindex)
		{
			$indexerCollection = $this->indexCollection->create();
			$indexids = $indexerCollection->getAllIds();
			foreach ($indexids as $indexid)
			{
				$indexidarray = $this->indexFactory->create()->load($indexid);
				//If you want reindex all use this code.
				 $indexidarray->reindexAll($indexid);
				 
				//If you want to reindex one by one, use this code
				// $indexidarray->reindexRow($indexid);
			}	
		}		
	}


}
