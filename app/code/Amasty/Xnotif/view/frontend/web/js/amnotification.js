/**
 *
 * @author    Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package   Amasty_Xnotif
 *
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "underscore",
    "mage/template",
    "priceUtils",
    "Magento_ConfigurableProduct/js/configurable",
    "priceBox",
    "jquery/ui",
    "jquery/jquery.parsequery",
    "mage/mage",
    "mage/validation",
    "Magento_Swatches/js/swatch-renderer"
], function ($, _, mageTemplate, utils, Component) {

    $.widget('mage.amnotification', {
        configurableStatus: null,
        spanElement: null,
        options: {},

        _create: function () {
            this.spanElement = $('.stock.available span')[0];
            this.settings = $('.swatch-option');
            this.dropdowns   = $('select.super-attribute-select, select.swatch-select');
            this._initialization();

            $(document).ready($.proxy(function () {
                var val = $('.super-attribute-select:last').val();
                if (val) {
                    $('.super-attribute-select:last').change();
                }
            }, this));
        },

        _removeStockStatus: function () {
            $('#amstockstatus-status').remove();
        },

        /**
         * remove stock alert block
         */
        _hideStockAlert: function () {
            $('#amstockstatus-stockalert').remove();
        },

        _reloadDefaultContent: function (key) {
            if (this.spanElement) {
                this.spanElement.innerHTML = this.configurableStatus;
            }
            $('.box-tocart').each(function (index, elem) {
                $(elem).show();
            });
        },

        showStockAlert: function (code) {
            var wrapper = $('.product-add-form')[0];
            // var beforeNode = wrapper.children()[0];
            var div = document.createElement('div');
            div.id = 'amstockstatus-stockalert';
            div.innerHTML = code;
            $(div).insertBefore($(wrapper));
            $('#form-validate-stock').mage('validation');
        },

        /*
         * configure statuses at product page
         */
        onConfigure: function () {
            this._hideStockAlert();
            if (null == this.configurableStatus && this.spanElement) {
                this.configurableStatus = $(this.spanElement).html();
            }
            //get current selected key
            var selectedKey = "";
            this.settingsForKey = $('select.super-attribute-select, div.swatch-option.selected, select.swatch-select');
            if (this.settingsForKey.length) {
                for (var i = 0; i < this.settingsForKey.length; i++) {
                    if (parseInt(this.settingsForKey[i].value) > 0) {
                        selectedKey += this.settingsForKey[i].value + ',';
                    }

                    if (parseInt($(this.settingsForKey[i]).attr('option-id')) > 0) {
                        selectedKey += $(this.settingsForKey[i]).attr('option-id') + ',';
                    }
                }
            }

            var trimSelectedKey = selectedKey.substr(0, selectedKey.length - 1);
            var countKeys = selectedKey.split(",").length - 1;

            /*reload main status*/
            if ('undefined' != typeof(this.options.xnotif[trimSelectedKey])) {
                this._reloadContent(trimSelectedKey);
            } else {
                this._reloadDefaultContent(trimSelectedKey);
            }

            var inputForPrice = $('#form-validate-price input[name="product_id"]');
            if (this.options.xnotif[trimSelectedKey] && inputForPrice.length) {
                inputForPrice.val(this.options.xnotif[trimSelectedKey]['product_id']);
            } else {
                var parentId = $('#form-validate-price input[name="parent_id"]').val();
                if (parentId && inputForPrice.length) {
                    $('#form-validate-price input[name="product_id"]').val();
                }
            }
            /*add statuses to dropdown*/
            var settings = this.settingsForKey;
            for (var i = 0; i < settings.length; i++) {
                if (!settings[i].options) {
                    continue;
                }

                for (var x = 0; x < settings[i].options.length; x++) {
                    if (!settings[i].options[x].value || settings[i].options[x].value == '0') {
                        continue;
                    }

                    if (countKeys == i + 1) {
                        var keyCheckParts = trimSelectedKey.split(',');
                        keyCheckParts[keyCheckParts.length - 1] = settings[i].options[x].value;
                        var keyCheck = keyCheckParts.join(',');
                    } else {
                        if (countKeys < i + 1) {
                            var keyCheck = selectedKey + settings[i].options[x].value;
                        }
                    }

                    if ('undefined' != typeof(this.options.xnotif[keyCheck])
                        && this.options.xnotif[keyCheck]
                    ) {
                        settings[i].options[x].disabled = false;
                        var status = this.options.xnotif[keyCheck]['custom_status'];
                        if (status) {
                            status = status.replace(/<(?:.|\n)*?>/gm, ''); // replace html tags
                            if (settings[i].options[x].text.indexOf(status) === -1) {
                                settings[i].options[x].text = settings[i].options[x].text + ' (' + status + ')';
                            }
                        } else {
                            var position = settings[i].options[x].text.indexOf('(');
                            if (position > 0) {
                                settings[i].options[x].text = settings[i].options[x].text.substring(0, position);
                            }
                        }
                    }
                }
            }

        },
        /*
         * reload default stock status after select option
         */
        _reloadContent: function (key) {
            this.spanElement = $('.stock.available span')[0];
            if ('undefined' != typeof(this.options.xnotif.changeConfigurableStatus)
                && this.options.xnotif.changeConfigurableStatus
                && this.spanElement
            ) {
                if (this.options.xnotif[key] && this.options.xnotif[key]['custom_status']) {
                    this.spanElement.innerHTML = this.options.xnotif[key]['custom_status'];
                } else {
                    this.spanElement.innerHTML = this.configurableStatus;
                }
            }

            if ('undefined' != typeof(this.options.xnotif[key])
                && this.options.xnotif[key]
                && 0 == this.options.xnotif[key]['is_in_stock']
            ) {
                $('.box-tocart').each(function (index, elem) {
                    $(elem).hide();
                });

                if (this.options.xnotif[key]['stockalert']) {
                    this.showStockAlert(this.options.xnotif[key]['stockalert']);
                }
            } else {
                $('.box-tocart').each(function (index, elem) {
                    $(elem).show();
                });
            }
        },

        _initialization: function () {
            var me = this;
            $(document).ready($.proxy(function () {
                setTimeout(function () { me.onConfigure(); }, 300);
            },this));

            $('body').on( {
                    'click': function (){
                        setTimeout(
                            function() {
                                me.onConfigure();
                            },
                            300
                        );
                    }
                },
                'div.swatch-option, select.super-attribute-select, select.swatch-select'
            ).on( {
                    'change': function (){
                        setTimeout(
                            function() {
                                me.onConfigure();
                            },
                            300
                        );
                    }
                },
                'select.super-attribute-select, select.swatch-select'
            );
        }
    });

    return $.mage.amnotification;
});
