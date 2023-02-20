<?php

namespace Bsitc\Brightpearl\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;

class Allmgtpaymentconfig extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;
    /**
     * @var Config
     */
    protected $_paymentModelConfig;
    
    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config               $paymentModelConfig
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig
    ) {

        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
    }
 
    public function toOptionArray()
    {
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = [];
        $finalarray = [];
        $finalarray[] = [ 'value' => 'notskip', 'label' => 'Not Skip' ];
        
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->_appConfigScopeConfigInterface
                ->getValue('payment/'.$paymentCode.'/title');
            $methods[] = [
                'label' => $paymentTitle,
                'value' => $paymentCode
            ];
        }

            
        foreach ($methods as $data) {
            if (!is_array($data['value'])) {
                $value  = $data['value'];
                $label  = $data['label'];
                $finalarray[] = ['value' => $value,'label' => $label];
            // $finalarray[$value] = $label;
            }
        }
            
            
            //$finalarray['store_credit'] = 'Store Credit';
            //$finalarray['gift_voucher'] = 'Gift Voucher';
            $finalarray[] = [ 'value' => 'store_credit', 'label' => 'Store Credit' ];
            $finalarray[] = [ 'value' => 'gift_voucher', 'label' => 'Gift Voucher' ];
            
            
            
            /* ------------- if pos module enable then added that method also  ------*/
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $moduleManager = $objectManager->create('\Magento\Framework\Module\Manager');
        if ($moduleManager->isOutputEnabled('Magestore_Webpos')) {
             $paymentModelSource = $objectManager->create('\Magestore\Webpos\Model\Source\Adminhtml\Payment');
            $paymentList = $paymentModelSource->getPosPaymentMethods();
            if (count($paymentList) > 0) {
                foreach ($paymentList as $item) {
                     $code     = $item['code'];
                    $title     = 'POS '.$item['title'];
                    $finalarray[] = ['value' => $code,'label' => $title];
                     // $finalarray[$code] = $title;
                }
            }
        }
            /* ------------- if pos module enable then added that method also  ------*/
              

        return $finalarray;
    }
}
