/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Magento_Checkout/js/model/quote',
    'MageWorx_MultiFees/js/model/shipping-fee',
    'MageWorx_MultiFees/js/action/apply-fees',
    'MageWorx_MultiFees/js/model/fee-messages',
    'mage/translate',
    'mage/url',
    'mageUtils',
    'uiRegistry',
    'MageWorx_MultiFees/js/form/element/select',
    'MageWorx_MultiFees/js/form/element/date',
    'MageWorx_MultiFees/js/form/element/checkbox-set',
    'MageWorx_MultiFees/js/form/element/textarea',
    'MageWorx_MultiFees/js/form/element/hidden'
], function ($, ko, Component, quote, fee, applyFeesAction, messageContainer, $t, urlBuilder, utils, Registry) {
    'use strict';

    var errorMessage = $t('You need to select required additional fees to proceed to checkout');
    var isLoading = ko.observable(false);

    /**
     * Format params
     *
     * @param {Object} params
     * @returns {string}
     */
    function prepareParams(params)
    {
        var result = '?';

        _.each(params, function (value, key) {
            result += key + '=' + value + '&';
        });

        return result.slice(0, -1);
    }

    return Component.extend({
        defaults: {
            reloadUrl: urlBuilder.build('multifees/checkout/refresh')
        },

        initialize: function () {
            var self = this;
            this._super();
            var childExists = this.elems().length > 0;
            this.shouldShow(childExists);

            quote.shippingMethod.subscribe(function (method) {
                if (method.carrier_code && method.method_code) {
                    var code = method.carrier_code + '_' + method.method_code;
                    self.updateContent(code);
                }
            }, null, 'change');

            return this;
        },

        isLoading: isLoading,

        onSubmit: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('mageworxShippingFeeForm.data.validate');

            if (!this.source.get('params.invalid')) {
                isLoading(true);
                var formData = this.source.get('mageworxShippingFeeForm');
                formData['type'] = 3;
                applyFeesAction(formData, isLoading, 'mageworx-shipping-fee-form');
            } else {
                messageContainer.addErrorMessage({'message': errorMessage});
            }
        },

        isDisplayTitle: function () {
            return false; //fee.allData().is_display_title;
        },

        shouldShow: ko.observable(false),

        /**
         * Update shipping fees by selected shipping method
         *
         * @param code
         * @returns {exports}
         */
        updateContent: function (code) {
            var content = this.reloadData(code),
                self = this,
                fieldset = self.getChild('mageworx-shipping-fee-form-fieldset');

            content.done(function (components) {
                // Before we do update elements in the from we are destroying the old ones
                if (fieldset.elems) {
                    fieldset.destroyChildren();
                }

                // When components are updated we should check is form should be visible
                // or not (empty elements or just hidden inputs)
                var visibleComponents = _.isEmpty(components) ?
                    [] :
                    components.filter(component => component.isVisibleInputType),
                    wholeFormVisibility = !_.isEmpty(visibleComponents);

                self.shouldShow(wholeFormVisibility);
                self.childCandidates = components;
                _.forEach(components, function (o, i) {
                    o.index = i;
                    components[i] = require(o.component)(_.extend(o, o.config));
                });
                fieldset.insertChild(components);
            });

            return this;
        },

        /**
         * Updates data from server.
         */
        reloadData: function (code) {
            var params = this.params,
                shippingData = {"shipping_method": code},
                data = this.getData() ? _.extend(this.getData(), shippingData) : shippingData,
                url = this.reloadUrl,
                save = $.Deferred();

            data['form_key'] = window.FORM_KEY;
            data = utils.serialize(data);

            if (!url) {
                save.resolve();
            }

            $('body').trigger('processStart');

            $.ajax({
                url: url + prepareParams(params),
                data: data,
                dataType: 'json',

                /**
                 * Success callback.
                 * @param {Object} resp
                 * @returns {Boolean}
                 */
                success: function (resp) {
                    if (resp.ajaxExpired) {
                        window.location.href = resp.ajaxRedirect;
                    }

                    if (!resp.error) {
                        save.resolve(resp);

                        return true;
                    }

                    $('body').notification('clear');
                    $.each(resp.messages, function (key, message) {
                        $('body').notification('add', {
                            error: resp.error,
                            message: message,

                            /**
                             * Inserts message on page
                             * @param {String} msg
                             */
                            insertMethod: function (msg) {
                                $('.page-main-actions').after(msg);
                            }
                        });
                    });
                },

                /**
                 * Complete callback.
                 */
                complete: function () {
                    $('body').trigger('processStop');
                }
            });

            return save.promise();
        },

        /**
         * Get all data used during refresh fee
         *
         * @returns {{shippingAddressData: {}}}
         */
        getData: function () {
            var shippingAddressData = quote.shippingAddress() ?
                _.pick(quote.shippingAddress(), function (value, key, object) {
                    return !_.isFunction(value);
                }) :
                {};

            return {
                "shippingAddressData": shippingAddressData
            };
        }
    });
});

