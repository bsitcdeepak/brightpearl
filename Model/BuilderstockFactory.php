<?php

namespace Bsitc\Brightpearl\Model;

class BuilderstockFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    public $_logManager;
    public $_scopeConfig;
	public $_productRepository;
	public $_searchCriteriaBuilder;
	public $_api;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Bsitc\Brightpearl\Model\Api $api
		
    ) {
        $this->_objectManager = $objectManager;
		$this->_logManager	= $logManager;
		$this->_scopeConfig	= $scopeConfig;
		$this->_productRepository = $productRepository;
		$this->_searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->_api = $api;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Builderstock', $arguments, false);
    }
	
    public function addRecord($row)
    {
        if (count($row)>0) {
            $record = $this->create();
            $record->setData($row);
            $record->save();
        }
        return true;
    }
    
    public function updateRecord($id, $row)
    {
        $record =  $this->create()->load($id);
         $record->setData($row);
        $record->setId($id);
        $record->save();
    }
    
    public function findRecord($column, $value)
    {
        $data = '';
        $collection = $this->create()->getCollection()->addFieldToFilter($column, $value);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
        }
        return $data;
    }

    public function removeAllRecord()
    {
        $collection = $this->create()->getCollection();
        $collection->walk('delete');
        return true;
    }
    
    public function removeRecord($id)
    {
        $record = $this->create()->load($id);
        if ($record) {
            $record->delete();
        }
        return true;
    }
	
	public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

	public function synchronize()
    {
		$this->removeAllRecord();
		$enable_builder_stock =  $this->getConfig('bpconfiguration/builders/enable_builder_stock');
		if($enable_builder_stock)
		{
			$this->synBuilderProducts();
			$this->synStockBuilderProducts();
			$this->updateMadeToOrderStatus();
		}		
	}
    
	public function synBuilderProducts()
	{
		/* $builders_attribute_set =  $this->getConfig('bpconfiguration/builders/attribute_set'); */ 
		$builders_attribute_set =  $this->getConfig('bpconfiguration/builders/buildersstock_attribute_set');
		if($builders_attribute_set)
		{
			$configAttributeSetArray = explode(",",$builders_attribute_set);
			$searchCriteria = $this->_searchCriteriaBuilder->addFilter('attribute_set_id', $configAttributeSetArray, 'in')->addFilter('type_id', 'bundle', 'eq')->create();
			$searchResults = $this->_productRepository->getList($searchCriteria);
			$products = $searchResults->getItems();
			if ($products)
			{
				$stockwarehouseforbuilder =  $this->getConfig('bpconfiguration/builders/stockwarehouseforbuilder');
				foreach($products as $product)
				{
					$optionArray = [];
					$optionsCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
					foreach ($optionsCollection as $options) {
						$optionArray[$options->getOptionId()]['option_title'] = $options->getDefaultTitle();
						$optionArray[$options->getOptionId()]['option_type'] = $options->getType();
					}
				
					$selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
					foreach ($selectionCollection as $proselection)
					{
						if( strtolower($optionArray[$proselection->getOptionId()]['option_title']) == strtolower('Fabric') )
						{
							$row = [];
							$row['parent_id'] = $product->getId();
							$row['parent_sku'] = $product->getSku();
							$row['parent_name'] = $product->getName();
							$row['parent_attribute_set_id'] = $product->getAttributeSetId();
							$row['product_id'] = $proselection->getId();
							$row['sku'] = $proselection->getSku();
							$row['name'] = $proselection->getName();
							$row['attribute_set_id'] = $proselection->getAttributeSetId();
							$row['warehouse'] = $stockwarehouseforbuilder;
							$row['qty'] = 0;
							$this->addRecord($row);
 						}
					}
				}
			}
		}
	}
	
	public function synBuilderProducts_old()
	{
		/* $builders_attribute_set =  $this->getConfig('bpconfiguration/builders/attribute_set'); */ 
		$builders_attribute_set =  $this->getConfig('bpconfiguration/builders/buildersstock_attribute_set');
		if($builders_attribute_set)
		{
			$configAttributeSetArray = explode(",",$builders_attribute_set);
			$searchCriteria = $this->_searchCriteriaBuilder->addFilter('attribute_set_id', $configAttributeSetArray, 'in')->addFilter('type_id', 'simple', 'eq')->create();
			$searchResults = $this->_productRepository->getList($searchCriteria);
			$products = $searchResults->getItems();
			if ($products)
			{
				$stockwarehouseforbuilder =  $this->getConfig('bpconfiguration/builders/stockwarehouseforbuilder');
				foreach($products as $product)
				{
					$row = [];
					$row['product_id'] = $product->getId();
					$row['sku'] = $product->getSku();
					$row['name'] = $product->getName();
					$row['attribute_set_id'] = $product->getAttributeSetId();
					$row['warehouse'] = $stockwarehouseforbuilder;
					$row['qty'] = 0;
					$this->addRecord($row);
				}
			}
		}
	}
 
	public function synStockBuilderProducts()
	{
		$collection = $this->create()->getCollection();
		if ($collection->getSize()) 
		{
			foreach($collection as $row)
			{
				$sku = trim($row->getSku());
				$bpProdictId = $this->_api->getProductIDFromSku($sku);
				if($bpProdictId)
				{
					$record = [];
					$record['bpid'] = $bpProdictId;
					$this->updateRecord($row['id'], $record);
					
					$result = $this->_api->getProductsAvailability($bpProdictId);
					if($result)
					{
						$bpStock = $result[$bpProdictId];
						if (array_key_exists("warehouses",$bpStock))
						{
							$stockwarehouseforbuilder =  $this->getConfig('bpconfiguration/builders/stockwarehouseforbuilder');
							foreach( $bpStock['warehouses'] as $warehouse=>$value )
							{
								if($warehouse == $stockwarehouseforbuilder)
								{
									$record = [];
									$record['qty'] =  $value['onHand'];
									$this->updateRecord($row['id'], $record);
									break;
								}
							}
						}
					}
				}
			}
		}
		
			// $this->_api->getProductIDFromSku($sku);
			// $this->_api->getProductsAvailability($range);

	}
	
	public function callUpdateMadeToOrderStatus()
	{
		$mto =  $this->_objectManager->create('Bsitc\Brightpearl\Model\Madetoorder');
		$mto->updateMtoCategoryProducts();
	}
	
	
	public function updateMadeToOrderStatus()
	{
		$mto =  $this->_objectManager->create('Bsitc\Brightpearl\Model\Madetoorder');
		$configuration          = $mto->_data;
		$motCategoryIdsArray    = explode(",", $configuration['mtocategories']);
		$ptoCategoryIdsArray    = explode(",", $configuration['ptocategories']);
		$mtoProductIds = $mto->getCategoryProductIds($motCategoryIdsArray);
		$ptoProductIds = $mto->getCategoryProductIds($ptoCategoryIdsArray);
		
		$collection = $this->create()->getCollection();
		$collection->addFieldToFilter('qty', ['gt'=>0]);
		if ($collection->getSize()) 
		{
			foreach ($collection as $item)
			{
				
				if (in_array( $item->getProductId() , $mtoProductIds ))
				{
					$mto->disableStockManagement($item->getProductId(), "is_madetoorder");
				}
				elseif (in_array( $item->getProductId() , $ptoProductIds ))
				{
					$mto->disableStockManagement($item->getProductId(), "is_printtoorder");
				}
				else{
					
				}	
			}		 
		}
	}
	
	
	
	
	
}


/*
$configAttributeSetArray = explode(",",$builders_attribute_set);
$collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
$productcollection = $collection->addAttributeToSelect('*')
->addAttributeToFilter('attribute_set_id', ['in'=>$configAttributeSetArray])
->load();
foreach($productcollection as $product){
echo '<pre>'; print_r($product->getData()); echo '</pre>';
//$isBuilderProduct = $helper->isBuilderProduct($items->getProduct()->getAttributeSetId());
}
*/


	

