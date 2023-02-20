<?php

namespace Bsitc\Brightpearl\Model;

class Cleaninventoryreservation extends \Magento\Framework\Model\AbstractModel
{
    public $_scopeConfig;
    public $_storeManager;
    public $_logManager; 
	protected $indexFactory;
	protected $indexCollection;
	protected $resourceConnection;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Bsitc\Brightpearl\Model\Logs $logManager,
		\Magento\Indexer\Model\IndexerFactory $indexFactory,
		\Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection		
    ) {
        $this->_storeManager	= $storeManager;
        $this->_scopeConfig		= $scopeConfig;
        $this->_logManager		= $logManager;
		$this->indexFactory 	= $indexFactory;
        $this->indexCollection 	= $indexCollection;
        $this->resourceConnection = $resourceConnection;
     }

	public function executeCron()
	{
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->_scopeConfig->getValue('bpconfiguration/bpcron/enable_clean_reservation', $storeScope);
		if($enable)
		{
			$table = $this->resourceConnection->getTableName('inventory_reservation');
			$this->resourceConnection->getConnection()->truncateTable($table);

			$indexerCollection = $this->indexCollection->create();
			$indexids = $indexerCollection->getAllIds();
			foreach ($indexids as $indexid)
			{
				$indexidarray = $this->indexFactory->create()->load($indexid);
				 $indexidarray->reindexAll($indexid);
			}
			return true;
		}		
	}


}
