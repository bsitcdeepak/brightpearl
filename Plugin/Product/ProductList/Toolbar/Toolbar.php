<?php 

namespace Bsitc\Brightpearl\Plugin\Product\ProductList;

class Toolbar
{
	/**
	* Plugin
	*
	* @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
	* @param \Closure $proceed
	* @param \Magento\Framework\Data\Collection $collection
	* @return \Magento\Catalog\Block\Product\ProductList\Toolbar
	*/
	public function aroundSetCollection(
		\Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
		\Closure $proceed,
		$collection
	) {
		$this->_collection = $collection;
		$currentOrder = $toolbar->getCurrentOrder();
		$currentDirection = $toolbar->getCurrentDirection();
		$result = $proceed($collection);

		if ($currentOrder) {
			switch ($currentOrder) {

			case 'newest':
				$this->_collection
					->getSelect()
					->order('e.position ASC');
			break;

			default:        
				$this->_collection
					->setOrder($currentOrder, $currentDirection);
			break;

			}
		}
		//var_dump((string) $this->_collection->getSelect());
		return $result;
	}
}