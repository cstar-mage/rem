define(
    [
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'MageWorx_MultiFees/summary/fee'
            },
            totals: quote.getTotals(),

            isDisplayed: function () {
                if (this.isFullMode()) {
                    var price = 0;
                    if (this.totals() && totals.getSegment('mageworx_shipping_fee')) {
                        price = totals.getSegment('mageworx_shipping_fee').value;
                        if (price > 0) {
                            return true;
                        }
                    }
                }
                return false;
            },
            getValue: function () {
                var price = 0;
                if (this.totals() && totals.getSegment('mageworx_shipping_fee')) {
                    price = totals.getSegment('mageworx_shipping_fee').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_fee;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            },
            formatPrice: function (price) {
                return this.getFormattedPrice(price);
            },
            getDetails: function () {
                var feeSegment = totals.getSegment('mageworx_shipping_fee');
                if (feeSegment && feeSegment.extension_attributes) {
                    var details = feeSegment.extension_attributes.mageworx_fee_details;
                    return details;
                }
                return [];
            }
        });
    }
);