/*global define*/
define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Magento_Checkout/js/model/quote',
    'MageWorx_MultiFees/js/model/fee',
    'MageWorx_MultiFees/js/action/apply-fees',
    'MageWorx_MultiFees/js/model/fee-messages',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    "Magento_Checkout/js/model/step-navigator",
    "Magento_Checkout/js/model/cart/totals-processor/default",
    'underscore'
], function (
    $,
    ko,
    Component,
    quote,
    fee,
    applyFeesAction,
    messageContainer,
    $t,
    getPaymentInformationAction,
    totals,
    stepnav,
    totalsProcessor,
    _
) {
    'use strict';

    var errorMessage = $t('You need to select required additional fees to proceed to checkout');
    var isLoading = ko.observable(false);

    return Component.extend({
        initialize: function () {
            this._super();
            return this;
        },

        isLoading: isLoading,

        onSubmit: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('mageworxFeeForm.data.validate');

            if (!this.source.get('params.invalid')) {
                isLoading(true);
                var formData = this.source.get('mageworxFeeForm');
                formData['type'] = 1;
                applyFeesAction(formData, isLoading, 'mageworx-fee-form');
            } else {
                messageContainer.addErrorMessage({'message': errorMessage});
                return this;
            }

            return this;
        },

        isDisplayTitle: function () {
            return fee.allData().is_display_title;
        },

        isDisplayed: function () {
            return fee.allData().is_enable;
        },

        updateTotals: function () {
            isLoading(false);
            // Reload totals if all done (cart page only)
            if (_.isEmpty(stepnav.steps())) {
                totalsProcessor.estimateTotals(quote.shippingAddress());
            }
            if (stepnav.getActiveItemIndex()) {
                var deferred = $.Deferred();
                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    totals.isLoading(false);
                });
            }
        }
    });
});
