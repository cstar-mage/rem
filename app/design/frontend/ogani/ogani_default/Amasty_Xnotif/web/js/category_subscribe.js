define([
    'jquery',
    'jquery/ui'
], function ($, ui) {

    $.widget('mage.categorySubscribe', {
        options: {
            selectors: {
                productInfoContainer: '.product-item-info',
                productInnerContainer: '.product-item-inner',
                amxnotifBlock: '.amxnotif-block',
                subscribePopup: '.category.subscribe-popup',
                closePopup: '.close-subscribe-popup'
            }
        },

        _create: function() {
            var isGuest = this.element.find(this.options.selectors.amxnotifBlock).length > 0,
                productInner = this.element.parents(this.options.selectors.productInfoContainer).first()
                    .find(this.options.selectors.productInnerContainer);
            if (productInner.length > 0) {
                //~ productInner.prepend(this.element);
            }

            if (this.options.usePopup == '1' && isGuest) {
                if (!this.options.popup) {
                    this.options.popup = this.element.find(this.options.selectors.subscribePopup);
                }
                this.element.find('a').on('click', function () {
                    this.options.popup.show();
                    window.onclick = function(event) {
                        if (event.target == this.options.popup[0]) {
                            this.closeSubscribePopup();
                        }
                    }.bind(this);
                }.bind(this));
                this.options.popup.find(this.options.selectors.closePopup).on('click', function() {
                    this.closeSubscribePopup();
                }.bind(this));
                $('body').append(this.options.popup);
            }

            if (!isGuest) {
                this.element.show();
            } else {
                this.element.find(this.options.selectors.amxnotifBlock).show();
            }
        },

        closeSubscribePopup: function () {
            this.options.popup.hide();
        }
    });

    return $.mage.categorySubscribe;
});
