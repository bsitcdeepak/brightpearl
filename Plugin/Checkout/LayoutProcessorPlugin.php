<?php
namespace Bsitc\Brightpearl\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;

class LayoutProcessorPlugin
{
    
    private $Allbpwarehouse;
    
    public function __construct(
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $Allbpwarehouse
    ) {
         $this->allbpwarehouse = $Allbpwarehouse;
    }

    
    public function afterProcess(
        LayoutProcessor $subject,
        array $jsLayout
    ) {

        $validation['required-entry'] = true;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['custom-shipping-method-fields']['children']['warehouse_store'] = [
           'component' => "Magento_Ui/js/form/element/select",
           'config' => [
               'customScope' => 'customShippingMethodFields',
               'template' => 'ui/form/field',
               'elementTmpl' => "ui/form/element/select",
               'id' => "warehouse_store"
           ],
           'dataScope' => 'customShippingMethodFields.custom_shipping_field[warehouse_store]',
           'label' => "Select warehouse",
           'options' => $this->getSelectOptions(),
           'caption' => 'Please select',
           'provider' => 'checkoutProvider',
           'visible' => true,
           'validation' => $validation,
           'sortOrder' => 4,
           'id' => 'custom_shipping_field[warehouse_store]'
        ];

        return $jsLayout;
    }

    public function getSelectOptions()
    {

        $data = $this->allbpwarehouse->toOptionArray();
        return $data;
      
        /*$items[1]["value"] = "First Value";
        $items[1]["label"] = "First Label";
        $items[2]["value"] = "Second Value";
        $items[2]["label"] = "Second Label";
        return $items;*/
    }
}
