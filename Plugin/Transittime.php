<?php

namespace Bsitc\Brightpearl\Plugin;

use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Bsitc\Brightpearl\Model\TransitmappingFactory;

class Transittime
{
    /**
     * @var ShippingMethodExtensionFactory
     */
    protected $extensionFactory;
    protected $tmf;

    /**
     * DeliveryDate constructor.
     * 
     * @param ShippingMethodExtensionFactory $extensionFactory
     */
    public function __construct(
		ShippingMethodExtensionFactory $extensionFactory,
		TransitmappingFactory $tmf
	)
    {
        $this->extensionFactory = $extensionFactory;
        $this->tmf = $tmf;
    }

    /**
     * Add delivery date information to the carrier data object
     * 
     * @param ShippingMethodConverter $subject
     * @param ShippingMethodInterface $result
     * @return ShippingMethodInterface
     */
    public function afterModelToDataObject(ShippingMethodConverter $subject, ShippingMethodInterface $result, $rateModel)
    {
		//$msg = " Carrier ". $rateModel->getCarrierTitle().' = '.$rateModel->getCarrier();
		//$msg .= " Method ". $rateModel->getMethodTitle().' = '.$rateModel->getMethod();
		/*
		$code = strtolower($rateModel->getCarrier().'_'.$rateModel->getMethod());  // code like : flatrate_flatrate
		if( $rateModel->getCarrier() == 'matrixrate'){
			$code = trim($rateModel->getMethodTitle());
			$code = preg_replace('/\s+/', '_', $code);
			$code = $rateModel->getCarrier().'_'.strtolower($code);  // code like : matrixrate_standard_road
		}
		$shippingAddress =  $rateModel->getAddress()->getQuote()->getShippingAddress();
		$countryCode = $shippingAddress->getData('country_id');
		
		$store_id = $rateModel->getAddress()->getQuote()->getStoreId();
		$msg = $this->tmf->findTransitTimeMsg( $code, $countryCode, $store_id );
		*/
		
		$msg = $this->tmf->findTransitTimeMsg( $rateModel );
		
        $extensibleAttribute =  ($result->getExtensionAttributes()) ? $result->getExtensionAttributes() : $this->extensionFactory->create();
        $extensibleAttribute->setTransittime($msg);
        $result->setExtensionAttributes($extensibleAttribute);
        return $result;
    } 
	
}