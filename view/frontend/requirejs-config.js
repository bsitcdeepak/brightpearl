var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Bsitc_Brightpearl/js/view/shipping': true
            }
        }
    },
    "map": {
        "*": {
            "Magento_Checkout/js/model/shipping-save-processor/default" : "Bsitc_Brightpearl/js/shipping-save-processor",
            list: 'Bsitc_Brightpearl/js/list',
            view: 'Bsitc_Brightpearl/js/view',
            bundleview: 'Bsitc_Brightpearl/js/bundleview'
			}
    }
};