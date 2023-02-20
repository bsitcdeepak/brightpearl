<?php

namespace Bsitc\Brightpearl\Model;

class TransitmappingFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Transitmapping', $arguments, false);
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
	
    public function findTransitTimeDefaultMsg($shipping_method, $country, $store_id)
    {
        $data = '';
        $collection = $this->create()->getCollection()->addFieldToFilter('shipping_method', $shipping_method)->addFieldToFilter('country', $country)->addFieldToFilter('store_id', $store_id);
        if ($collection->getSize()) {
            $item  = $collection->getFirstItem();
			$data = $item->getTransitTimeMsg(); 
        }
        return $data;
    }
	
    public function findTransitTimeMsg($rateModel)
    {
		$code = strtolower($rateModel->getCarrier().'_'.$rateModel->getMethod());  // code like : flatrate_flatrate
		if( $rateModel->getCarrier() == 'matrixrate'){
			$code = trim($rateModel->getMethodTitle());
			$code = preg_replace('/\s+/', '_', $code);
			$code = $rateModel->getCarrier().'_'.strtolower($code);  // code like : matrixrate_standard_road
		}
		$shippingAddress =  $rateModel->getAddress()->getQuote()->getShippingAddress();
		$countryCode = $shippingAddress->getData('country_id');
		$store_id = $rateModel->getAddress()->getQuote()->getStoreId();
		
		$quote = $rateModel->getAddress()->getQuote();
		$chk_pre_order = false;
		$chk_madeto_order = false;
		$chk_printto_order = false;
		$chkQuoteItemArray = array();
        foreach ($quote->getAllItems() as $item)
		{
            $quoteItems[$item->getId()] = $item;
            switch ($item->getItemTypeInfo()) {
                case 1:
					$chkQuoteItemArray['pre'][] = $item->getExpDeliveryDate();
                    $chk_pre_order = true;
                    break;
                case 2:
					$chkQuoteItemArray['mto'][] = $item->getLeadTimeTxt();
                    $chk_madeto_order = true;
                    break;
                case 3:
					$chkQuoteItemArray['pto'][] = $item->getLeadTimeTxt();
                    $chk_printto_order = true;
                    break;
            }
        }
		
        if ($chk_pre_order == true) {
            $exp_delivery_date = $chkQuoteItemArray['pre'][0];
			$msg = __('Expected Delivery %1', $exp_delivery_date);
         } elseif ($chk_madeto_order == true) {
            $exp_delivery_date = $chkQuoteItemArray['mto'][0];
			$msg = __('Expected Delivery %1', $exp_delivery_date);
        } elseif ($chk_printto_order == true) {
            $exp_delivery_date = $chkQuoteItemArray['pto'][0];
			$msg = __('Expected Delivery %1', $exp_delivery_date);
        } else {
			$msg = $this->findTransitTimeDefaultMsg( $code, $countryCode, $store_id );
        }
		return $msg ;
    }

}
