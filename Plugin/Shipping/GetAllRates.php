<?php

namespace Bsitc\Brightpearl\Plugin\Shipping;

class GetAllRates
{
 
    /**
     * Disable the marked shipping rates.
     *
     * NOTE: If you can not see some of the shipping rates, start debugging from here. At first, check 'is_disabled'
     * param in the shipping rate object.
     *
     * @param \Magento\Shipping\Model\Rate\Result $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllRates($subject, $result)
    {
        foreach ($result as $key => $rate) {
            if ($rate->getIsDisabled()) {
                unset($result[$key]);
            }
        }
 
        return $result;
    }
}