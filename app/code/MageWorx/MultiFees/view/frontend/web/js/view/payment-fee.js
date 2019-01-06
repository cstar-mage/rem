/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/form/form',
    'Magento_Checkout/js/model/quote',
    'MageWorx_MultiFees/js/model/payment-fee',
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
], function ($, ko, _, Component, quote, fee, applyFeesAction, messageContainer, $t, urlBuilder, utils, Registry) {
    'use strict';

    var errorMessage = $t('You need to select required additional fees to proceed to checkout');
    var isLoading = ko.observable(false);

    /**
     * Object comparison function
     *
     * @author crazyx
     * @source https://stackoverflow.com/a/1144249/4091669
     *
     * @returns {boolean}
     */
    function deepCompare()
    {
        var i, l, leftChain, rightChain;

        function compare2Objects(x, y)
        {
            var p;

            // remember that NaN === NaN returns false
            // and isNaN(undefined) returns true
            if (isNaN(x) && isNaN(y) && typeof x === 'number' && typeof y === 'number') {
                return true;
            }

            // Compare primitives and functions.
            // Check if both arguments link to the same object.
            // Especially useful on the step where we compare prototypes
            if (x === y) {
                return true;
            }

            // Works in case when functions are created in constructor.
            // Comparing dates is a common scenario. Another built-ins?
            // We can even handle functions passed across iframes
            if ((typeof x === 'function' && typeof y === 'function') ||
                (x instanceof Date && y instanceof Date) ||
                (x instanceof RegExp && y instanceof RegExp) ||
                (x instanceof String && y instanceof String) ||
                (x instanceof Number && y instanceof Number)) {
                return x.toString() === y.toString();
            }

            // At last checking prototypes as good as we can
            if (!(x instanceof Object && y instanceof Object)) {
                return false;
            }

            if (x.isPrototypeOf(y) || y.isPrototypeOf(x)) {
                return false;
            }

            if (x.constructor !== y.constructor) {
                return false;
            }

            if (x.prototype !== y.prototype) {
                return false;
            }

            // Check for infinitive linking loops
            if (leftChain.indexOf(x) > -1 || rightChain.indexOf(y) > -1) {
                return false;
            }

            // Quick checking of one object being a subset of another.
            // todo: cache the structure of arguments[0] for performance
            for (p in y) {
                if (y.hasOwnProperty(p) !== x.hasOwnProperty(p)) {
                    return false;
                } else if (typeof y[p] !== typeof x[p]) {
                    return false;
                }
            }

            for (p in x) {
                if (y.hasOwnProperty(p) !== x.hasOwnProperty(p)) {
                    return false;
                } else if (typeof y[p] !== typeof x[p]) {
                    return false;
                }

                switch (typeof (x[p])) {
                    case 'object':
                    case 'function':

                        leftChain.push(x);
                        rightChain.push(y);

                        if (!compare2Objects(x[p], y[p])) {
                            return false;
                        }

                        leftChain.pop();
                        rightChain.pop();
                        break;

                    default:
                        if (x[p] !== y[p]) {
                            return false;
                        }
                        break;
                }
            }

            return true;
        }

        if (arguments.length < 1) {
            return true; //Die silently? Don't know how to handle such case, please help...
            // throw "Need two or more arguments to compare";
        }

        for (i = 1, l = arguments.length; i < l; i++) {
            leftChain = []; //Todo: this can be cached
            rightChain = [];

            if (!compare2Objects(arguments[0], arguments[i])) {
                return false;
            }
        }

        return true;
    }

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

        currentMethod: null,
        address: null,
        isLoading: isLoading,
        shouldShow: ko.observable(false),

        initialize: function () {
            this._super();
            this.shouldShow(this.elems().length > 0);
            this.initSubscriptions();

            return this;
        },

        processTimer: false,

        /**
         * Subscriptions to the observable properties of foreign objects
         * on which this class depends
         */
        initSubscriptions: function () {
            var self = this;

            /**
             * Update content if the payment method was changed
             */
            quote.paymentMethod.subscribe(function (method) {
                if (method && method["method"] && self.currentMethod != method["method"]) {
                    self.currentMethod = method["method"];
                    self.updateContent();
                }
            }, null, 'change');

            /**
             * Update content if the billing address was changed
             */
            quote.billingAddress.subscribe(function (newAddress) {
                if (newAddress != null) {
                    if (!self.address) {
                        self.address = newAddress;
                        self.updateContent();
                    } else if (!self.isAddressesEqual(self.address, newAddress)) {
                        self.address = newAddress;
                        if (self.processTimer) {
                            clearTimeout(self.processTimer);
                        }
                        self.processTimer = setTimeout(function () {
                            self.updateContent();
                        }, 1000);
                    }
                }
            });
        },

        /**
         * Compare 2 address
         *
         * @param address1
         * @param address2
         * @returns {boolean}
         */
        isAddressesEqual: function (address1, address2) {
            var comparingAddress1 = Object.assign({}, address1),
                comparingAddress2 = Object.assign({}, address2);

            return deepCompare(comparingAddress1, comparingAddress2);
        },

        onSubmit: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('mageworxPaymentFeeForm.data.validate');

            if (!this.source.get('params.invalid')) {
                this.clearSource();
                var formData = this.source.get('mageworxPaymentFeeForm');
                if (formData) {
                    isLoading(true);
                    formData['type'] = 2;
                    formData = Object.assign(formData, utils.serialize(this.getDataForRequest()));
                    applyFeesAction(formData, isLoading, 'mageworx-payment-fee-form');
                }
            } else {
                messageContainer.addErrorMessage({'message': errorMessage});
            }
        },

        /**
         * Clearing the data corresponding to removed fee's data from the source to prevent mismatching on the backend during update fee request
         */
        clearSource: function () {
            var sourceData = this.source.get('mageworxPaymentFeeForm'),
                elements = Registry.get(
                    {
                        "name": 'checkout.steps.billing-step.payment.beforeMethods.mageworx-payment-fee-form-container.mageworx-payment-fee-form-fieldset'
                    }
                ).elems()
                    .filter(
                        function (el) {
                            return el.feeType == 2;
                        }
                    );

            for (var key in sourceData) {
                if (key === 'type') {
                    continue;
                }

                var found = false;
                for (var elementKey in elements) {
                    if (elements[elementKey].inputName === key) {
                        found = true;
                        break;
                    }
                }

                if (!found && sourceData.hasOwnProperty(key)) {
                    delete sourceData[key];
                }
            }
        },

        isDisplayTitle: function () {
            return fee.allData().is_display_title;
        },

        /**
         * Update payment fees by selected payment method
         *
         * @returns {exports}
         */
        updateContent: function () {
            var content = this.reloadData(),
                self = this,
                fieldset = self.getChild('mageworx-payment-fee-form-fieldset');

            if (!content || !fieldset) {
                return this;
            }

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
                self.onSubmit();
            });

            return this;
        },

        /**
         * Updates data from server.
         */
        reloadData: function () {
            if (!this.currentMethod) {
                // Empty payment method - do nothing
                return;
            }

            var params = this.params,
                data = utils.serialize(this.getDataForRequest()),
                url = this.reloadUrl,
                save = $.Deferred();

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
        getDataForRequest: function () {
            var billingAddressData = quote.billingAddress() ?
                _.pick(quote.billingAddress(), function (value, key, object) {
                    return !_.isFunction(value);
                }) :
                {};

            return {
                "billingAddressData": billingAddressData,
                "form_key": window.FORM_KEY,
                "payment_method": this.currentMethod
            };
        }
    });
});

