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
                template: 'MageWorx_MultiFees/summary/fee-tax'
            },
            totals: quote.getTotals(),

            isDisplayed: function () {
                if (this.isFullMode()) {
                    var price = 0;
                    if (this.totals() && totals.getSegment('mageworx_shipping_fee_tax')) {
                        price = totals.getSegment('mageworx_fee_tax').value;
                        if (price > 0) {
                            return true;
                        }
                    }
                }
                return false;
            },
            getValue: function () {
                var price = 0;
                if (this.totals() && totals.getSegment('mageworx_shipping_fee_tax')) {
                    price = totals.getSegment('mageworx_shipping_fee_tax').value;
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
                var feeSegment = totals.getSegment('mageworx_shipping_fee_tax');
                if (feeSegment && feeSegment.extension_attributes) {
                    var details = feeSegment.extension_attributes.mageworx_fee_details;
                    return details;
                }
                return [];
            }
        });
    }
);