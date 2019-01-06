define([
    'underscore',
    'mage/template',
    'Magento_Ui/js/modal/confirm',
    'prototype',
], function(_, mageTemplate, confirm) {
        var schedule = {
            rowTemplate: null,
            htmlId: '',
            itemsCount: 0,
            config: {

            },
            init: function(tpl, htmlId)
            {
                this.rowTemplate = mageTemplate(tpl);
                this.htmlId = htmlId;
            },
            getId: function(key, index){
                return this.htmlId + '_' + (index !== undefined ? index + '_' : '') + key;
            },
            findObject: function(key, index)
            {
                return $(this.getId(key, index));
            },
            setValue: function(key, index, value)
            {
                var object = this.findObject(key, index);
                if (object) {
                    if (object.type == 'checkbox'){
                        object.checked = value == 1? true : false;
                    } else {
                        object.setValue(value);
                    }
                }
            },
            addItem: function (config)
            {
                if (config){
                    this.config = config;
                } else {
                    this.config = {
                        expired_in_days: 4
                    }
                }
                var data = {
                    index: this.itemsCount++
                };

                var row = Element.insert(this.findObject('container'), {
                    bottom : this.rowTemplate({
                        data: data
                    })
                });

                this.configure(data.index);
            },
            configure: function(index)
            {
                _.each(this.config, function(value, key) {
                    this.setValue(key, index, value)
                }.bind(this));

                this.useShoppingCartRule(null, index);
                this.showLess(null, index);
            },
            useShoppingCartRule: function(event, index)
            {
                var couponOwn = this.findObject('coupon_own', index);
                var couponCartRule = this.findObject('coupon_cart_rule', index);

                var useShoppingCartRule = this.findObject('use_shopping_cart_rule', index);

                if (this.findObject('use_shopping_cart_rule', index).checked) {
                    couponCartRule.show();
                    couponOwn.hide();
                } else {
                    couponCartRule.hide();
                    couponOwn.show();
                }
            },
            showMore: function(event, index){
                var couponExrta = this.findObject('coupon_extra', index);
                var showMore = this.findObject('show_more', index);
                var showLess = this.findObject('show_less', index);

                couponExrta.show();
                showMore.hide();
                showLess.show();
            },
            showLess: function(event, index){
                var couponExrta = this.findObject('coupon_extra', index);
                var showMore = this.findObject('show_more', index);
                var showLess = this.findObject('show_less', index);

                couponExrta.hide();
                showMore.show();
                showLess.hide();
            },
            deleteItem: function(event, index){


                confirm({
                    content: 'Are you sure?',
                    actions: {
                        confirm: function () {
                            var schedule = this.findObject('schedule', index);
                            schedule.remove();
                        }.bind(this)
                    }
                })
            }
        }
        return schedule
    }
)
