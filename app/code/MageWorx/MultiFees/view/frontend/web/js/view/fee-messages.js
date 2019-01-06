/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'Magento_Ui/js/view/messages',
    '../model/fee-messages'
], function (Component, messageContainer) {
    'use strict';

    return Component.extend({
        initialize: function (config) {
            return this._super(config, messageContainer);
        }
    });
});

