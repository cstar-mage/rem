define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'MageWorx_MultiFees/js/model/fee'
    ],
    function (Component, additionalValidators, fee) {
        'use strict';
        additionalValidators.registerValidator(fee);
        return Component.extend({});
    }
);