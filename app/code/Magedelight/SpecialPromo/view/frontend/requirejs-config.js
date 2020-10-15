var config = {
    shim: {
        'Magedelight_SpecialPromo/js/applyCoupon':
                {
                    deps: [
                        'jquery',
                        'Magento_Checkout/js/model/quote',
                        'Magento_Checkout/js/model/resource-url-manager'
                    ]
                }
    },
    map: {
        '*': {
            'applyCoupon': 'Magedelight_SpecialPromo/js/applyCoupon',
            "Magento_Checkout/template/summary/totals.html": "Magedelight_SpecialPromo/template/totals.html"
        }
    }
};