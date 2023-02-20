<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

class Allmgtshipping extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;
    /**
     * @var Config
     */
    protected $_shippingModelConfig;

    protected $_moduleManager;
    
    protected $_objectManager;
 
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $appConfigScopeConfigInterface,
        \Magento\Shipping\Model\Config $shippingModelConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_shippingModelConfig = $shippingModelConfig;
        $this->_objectManager = $objectManager;
    }
 
    public function toOptionArray()
    {
        $activeCarriers = $this->_shippingModelConfig->getActiveCarriers();
        $methods = [];
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = [];
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code    = $carrierCode.'_'.$methodCode;
                    $options   = $code;
                    break;
                }
                $carrierTitle =$this->_appConfigScopeConfigInterface->getValue('carriers/'.$carrierCode.'/title');
            }
            $methods[] = ['value' => $options, 'label' =>  $carrierTitle];
        }
        $finalarray = [];
        foreach ($methods as $data) {
            if (!is_array($data['value'])) {
                  $value  = $data['value'];
                  $label  = $data['label'];
                  $finalarray[$value] = $label;
            }
        }
        return $finalarray;
    }
    
    public function getMgtShippinngOptionCustomArray($website = null)
    {
        $activeCarriers = $this->_shippingModelConfig->getActiveCarriers();
        $methods = [];
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = [];
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code    = $carrierCode.'_'.$methodCode;
                    $options    = $code;
                    break;
                }
                $carrierTitle =$this->_appConfigScopeConfigInterface->getValue('carriers/'.$carrierCode.'/title');
            }
            if ($options == 'matrixrate_matrixrate') {
                /* -------------- webshop matrix ---------------------*/
                 $matrixCollection = $this->_objectManager->create('\WebShopApps\MatrixRate\Model\ResourceModel\Carrier\Matrixrate\Collection');
                foreach ($matrixCollection as $item) {
                    $title = $carrierTitle .' ( '.trim($item->getShippingMethod()). ' )';
                    $value = 'matrixrate_'.trim($item->getShippingMethod());
					$value = preg_replace('/\s+/', '_', $value);
					$value = strtolower($value);
                    $methods[] = ['value' => $value, 'label' =>  $title];
                }
                /* -------------- webshop matrix ---------------------*/
            } else {
                $methods[] = ['value' => $options, 'label' =>  $carrierTitle];
            }
        }
        $finalarray = [];
        foreach ($methods as $data) {
            if (!is_array($data['value'])) {
                  $value  = $data['value'];
                  $label  = $data['label'];
                  $finalarray[$value] = $label;
            }
        }
        return $finalarray;
    }
}
