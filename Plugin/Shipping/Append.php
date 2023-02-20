<?php

namespace Bsitc\Brightpearl\Plugin\Shipping;

class Append
{

    protected $fsHelper;
	
    protected $session;
 
 
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
		\Bsitc\Brightpearl\Helper\Freeshipping $fsHelper,
        \Magento\Framework\App\State $state
    ) {
        if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session = $backendQuoteSession;
        } else {
            $this->session = $checkoutSession;
        }
		$this->fsHelper = $fsHelper;
    }
 
    /**
     * Validate each shipping method before append.
     * Apply the rules action if validation was successful.
     * Can mark some rules as disabled. The disabled rules will be removed in the class
     * @see MageWorx\ShippingRules\Model\Plugin\Shipping\Rate\Result\GetAllRates
     * by checking the value of this mark in the rate object.
     *
     * NOTE: If you have some problems with the rules and the shipping methods, start debugging from here.
     *
     * @param \Magento\Shipping\Model\Rate\Result $subject
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult|\Magento\Shipping\Model\Rate\Result $result
     * @return array
     */
    public function beforeAppend($subject, $result)
    {
        if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            return [$result];
        }
		$code = $result->getCarrier();
		// $methodCode = $result->getCarrier() . '_' . $result->getMethod();
		$quote = $this->session->getQuote();
		$isAllowOnlyFreeShipping = $this->fsHelper->isAllowFreeShipping($quote);
		// $this->fsHelper->recordLog('isAllowOnlyFreeShipping', $isAllowOnlyFreeShipping, 'isAllowOnlyFreeShipping');
		if($isAllowOnlyFreeShipping)
		{
			// $this->fsHelper->recordLog('Restircted Shipping', $code, 'code');
			if ( $code != 'freeshipping') {
			   $result->setIsDisabled(true);
			}
		}else{
			$result->setIsDisabled(false);
			if ( $code == 'freeshipping') {
			   $result->setIsDisabled(true);
			}			
		}
        return [$result];
    }
}