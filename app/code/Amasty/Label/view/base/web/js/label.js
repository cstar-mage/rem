define([
    "jquery",
    "jquery/ui"
], function ($) {

    $.widget('mage.amShowLabel', {
        options: {},
        textElement: null,
        image: null,
        imageWidth: null,
        imageHeight: null,
        parent: null,

        _create: function () {
            this.element     = $(this.element);

            /* code for moving product label*/
            if (this.options.move == '1') {
                var items = $(this.getPriceSelector());
                if (items.length) {// find any element with product identificator
                    var priceContainer = items.first(),
                        newParent = this.getNewParent(priceContainer);

                    if (newParent && newParent.length) {
                        priceContainer.attr('label-observered-' + this.options.label, '1');
                        newParent.append(this.element);
                    } else {
                        this.element.hide();
                        return;
                    }
                } else {
                    this.element.hide();
                    return;
                }
            }

            this.image       = this.element.find('.amasty-label-image');
            this.textElement = this.element.find('.amasty-label-text');
            this.parent      = this.element.parent();

            if (!this.image.length) {
                this.setStyleIfNotExist(
                    this.element,
                    {
                        'width': '100px',
                        'height': '50px'
                    }
                );
            }

            /* move label to container from settings*/
            if (this.options.path && this.options.path != "") {
                var newParent = this.parent.find(this.options.path);
                if (newParent.length) {
                    this.parent = newParent;
                    newParent.append(this.element);
                }
            }

            /*required for child position absolute*/
            if (!(this.parent.css('position') == 'absolute' || this.parent.css('position') == 'relative')) {
                this.parent.css('position', 'relative');
            }
            if (this.parent.prop("tagName") == "A") {
                this.parent.css('display', 'inline-block');
            }

            /*fix issue with hover on product grid*/
            this.element.closest('.product-item-info').css('zIndex', '2000');


            /* observe zoom load event for moving label*/
            this.productPageZoomEvent();

            /*get default image size*/
            if (this.imageLoaded(this.image)) {
                var me = this;
                this.image.load(function () {
                    me.element.fadeIn();
                    me.imageWidth = this.naturalWidth;
                    me.imageHeight = this.naturalHeight;
                });
            } else {
                this.element.fadeIn();
                if (this.image[0]) {
                    this.imageWidth = this.image[0].naturalWidth;
                    this.imageHeight = this.image[0].naturalHeight;
                }
            }

            this.setLabelStyle();
            this.setLabelPosition();
        },

        imageLoaded: function (img) {
            if (!img.complete) {
                return false;
            }

            if (typeof img.naturalWidth !== "undefined" && img.naturalWidth === 0) {
                return false;
            }

            return true;
        },

        productPageZoomEvent: function () {
            if (this.options.mode == 'prod') {
                var amlabelObject = this;

                $(document).on('fotorama:load', function (event) {
                    if (amlabelObject && amlabelObject.options.path && amlabelObject.options.path != "") {
                        var newParent = amlabelObject.parent.find(amlabelObject.options.path);
                        if (newParent.length && newParent != amlabelObject.parent) {
                            amlabelObject.parent.css('position', '');
                            amlabelObject.parent = newParent;
                            newParent.append(amlabelObject.element);
                            newParent.css('position', 'relative');
                        }
                    }
                });

                $(window).resize(function () {
                    setTimeout(
                        function () {
                            amlabelObject.setLabelStyle();
                            amlabelObject.setLabelPosition();
                        } ,
                        500
                    );
                });
            }
        },

        setStyleIfNotExist: function (element, styles) {
            for(style in styles) {
                var current = element.attr('style');
                if (!current ||
                    (current.indexOf('; ' + style) == -1 && current.indexOf(';' + style) == -1)
                ) {
                    element.css(style, styles[style]);
                }
            }

        },

        setLabelStyle: function () {
            /*for text element*/
            this.setStyleIfNotExist(
                this.textElement,
                {
                'padding'    : '0 3px',
                'position'   : 'absolute',
                'white-space' : 'nowrap',
                'width'      : '100%'
            });
            if (this.image.length) {
                /*for image*/
                this.image.css({
                    'width': '100%'
                });

                /*get block size depend settings*/
                if (this.options.size) {
                    var parentWidth = parseInt(this.parent.css('width').replace(/\D+/g, ""))
                    if (parentWidth && this.options.size > 0) {
                        var nativeWidth = this.imageWidth;
                        this.imageWidth = parentWidth * this.options.size / 100;
                    }
                } else {
                    this.imageWidth = this.imageWidth + 'px';
                }
                this.setStyleIfNotExist(this.element, {'width': this.imageWidth});
                this.imageHeight = this.image.height();

                /*if container doesn't load(height = 0 ) set proportional height*/
                if (!this.imageHeight && this.image[0] && 0 != this.image[0].naturalWidth) {
                    var tmpWidth = this.image[0].naturalWidth;
                    var tmpHeight = this.image[0].naturalHeight;
                    this.imageHeight = parseFloat(this.imageWidth) * (tmpHeight / tmpWidth);
                }
                var lineCount = this.textElement.html().split('<br>').length;
                lineCount = lineCount >= 1? lineCount: 1;
                this.textElement.css('lineHeight', this.imageHeight/lineCount + 'px');
                /*for whole block*/
                this.setStyleIfNotExist(this.element, {
                    'height': this.imageHeight + 'px'
                });

                if (this.options.size) {
                    //set responsive font size
                    var flag = 1;
                    this.textElement.css({'width': 'auto'});
                    while(this.textElement.width() > 0.9 * this.textElement.parent().width() && flag++ < 15) {
                        this.textElement.css({'fontSize': (100 - flag * 5) + '%'});
                    }
                    this.textElement.css({'width': '100%'});
                }
            }

            this.setStyleIfNotExist(this.element, {
                'line-height': 'normal',
                'position': 'absolute',
                'z-index': 995
            });

            this.element.click(function () {
                $(this).parent().trigger('click');
            });
        },

        setPosition: function (position) {
            this.options.position = position;
            this.element.css("top", "").css("left", "").css("right", "").css("bottom", "");
            this.element.css("margin-top", "").css("margin-bottom", "").css("margin-left", "").css("margin-right", "");
            this.setLabelPosition();
        },

        setLabelPosition: function () {
            switch (this.options.position) {
                case 'top-left':
                    this.setStyleIfNotExist(this.element, {
                        'top'  : 0,
                        'left' : 0
                    });
                    break;
                case 'top-center':
                    this.setStyleIfNotExist(this.element, {
                        'top': 0,
                        'left': 0,
                        'right': 0,
                        'margin-left': 'auto',
                        'margin-right': 'auto'
                    });
                    break;
                case 'top-right':
                    this.setStyleIfNotExist(this.element, {
                        'top'   : 0,
                        'right' : 0
                    });
                    break;

                case 'middle-left':
                    this.setStyleIfNotExist(this.element, {
                        'left' : 0,
                        'top'   : 0,
                        'bottom'  : 0,
                        'margin-top': 'auto',
                        'margin-bottom': 'auto'
                    });
                    break;
                case 'middle-center':
                    this.setStyleIfNotExist(this.element, {
                        'top'   : 0,
                        'bottom'  : 0,
                        'margin-top': 'auto',
                        'margin-bottom': 'auto',
                        'left': 0,
                        'right': 0,
                        'margin-left': 'auto',
                        'margin-right': 'auto'
                    });
                    break;
                case 'middle-right':
                    this.setStyleIfNotExist(this.element, {
                        'top'   : 0,
                        'bottom'  : 0,
                        'margin-top': 'auto',
                        'margin-bottom': 'auto',
                        'right' : 0
                    });
                    break;

                case 'bottom-left':
                    this.setStyleIfNotExist(this.element, {
                        'bottom'  : 0,
                        'left'    : 0
                    });
                    break;
                case 'bottom-center':
                    this.setStyleIfNotExist(this.element, {
                        'bottom': 0,
                        'left': 0,
                        'right': 0,
                        'margin-left': 'auto',
                        'margin-right': 'auto'
                    });
                    break;
                case 'bottom-right':
                    this.setStyleIfNotExist(this.element, {
                        'bottom'   : 0,
                        'right'    : 0
                    });
                    break;
            }
        },

        getNewParent: function (item) {
            var imageContainer = null,
                productContainer = item.parents('.item');

            if (productContainer.length == 0) {
                productContainer = item.parents('.product-item');
            }
            if (productContainer && productContainer.length) {
                imageContainer = productContainer.find(this.options.path);
            }

            return imageContainer;
        },

        getPriceSelector: function () {
            var notLabelObservered = ':not([label-observered-' + this.options.label + '])',
                selector = '[data-product-id="' + this.options.product + '"]' + notLabelObservered + ', ' +
                    '[id="product-price-' + this.options.product + '"]' + notLabelObservered + ', ' +
                    '[name="product"][value="' + this.options.product + '"]' + notLabelObservered;

            return selector;
        }
    });

    return $.mage.amShowLabel;
});
