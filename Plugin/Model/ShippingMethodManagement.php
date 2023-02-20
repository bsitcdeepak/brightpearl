<?php

namespace Bsitc\Brightpearl\Plugin\Model;

class ShippingMethodManagement {
	
	protected $fsHelper;
    protected $session;
	protected $moduleManager;
	
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,		
		\Magento\Checkout\Model\Cart $checkoutSession,
		\Magento\Backend\Model\Session\Quote $backendQuoteSession,
		\Bsitc\Brightpearl\Helper\Freeshipping $fsHelper,
		\Magento\Framework\Module\Manager $moduleManager,
		\Magento\Framework\App\State $state	
	)
    {
        $this->_storeManager = $storeManager;		
		if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session = $backendQuoteSession;			
        } else {
            $this->session = $checkoutSession;
        }
		$this->fsHelper = $fsHelper;
		$this->moduleManager = $moduleManager;
    }		
 
    public function afterEstimateByExtendedAddress($shippingMethodManagement, $output)
    {
        return $this->filterOutput($output);
    }
	
	public function afterEstimateByAddressId($shippingMethodManagement, $output)
    {
        return $this->filterOutput($output);
    }
	
	public function afterEstimateByAddress($shippingMethodManagement, $output)
    {
        return $this->filterOutput($output);
    }
	
    private function filterOutput($output)
    {		
		$isAllowOnlyFreeShipping  = false;		
		$disableAllFurnitureShipping = false;		
		$quote = $this->session->getQuote();
		$isAllowOnlyFreeShipping = $this->fsHelper->isAllowFreeShipping($quote);		
		if($this->moduleManager->isOutputEnabled('Magecomp_Extrafee')) 
		{
			$isAllowFurnitureShipping = $this->fsHelper->isAllowFurnitureShipping($quote);
			if(!$isAllowFurnitureShipping) {
				$disableAllFurnitureShipping = true;
			}
		}		
		$free = [];	
		$excludeFree = [];			
		if($disableAllFurnitureShipping){
			//return $output;
		}
        foreach ($output as $shippingMethod)
		{
			if($isAllowOnlyFreeShipping){
				if ($shippingMethod->getCarrierCode() == 'freeshipping' && $shippingMethod->getMethodCode() == 'freeshipping') {
					$free[] = $shippingMethod;
				}
			}else{
				if ($shippingMethod->getCarrierCode() != 'freeshipping' && $shippingMethod->getMethodCode() != 'freeshipping') {
					$excludeFree[] = $shippingMethod;
				}
			}	
        }
        if(count($free)) {		
            return $free;
        }
		//if(count($excludeFree)) {
            return $excludeFree;
       // }	
        return $output;
    }

}