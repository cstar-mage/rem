define([
    'jquery',
    'Magento_Ui/js/form/element/select'
], function ($, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input'
        },
        /**
         * Change currently selected option
         *
         * @param {String} id
         */
        selectOption: function (id) {
            if (($("#"+id).val() == 0) || ($("#"+id).val() == undefined)) {
                $('div[data-index="recurring_by"]').hide();
                $('div[data-index="recurring_intervals"]').hide();
                $('div[data-index="recurring_occurrences"]').hide();
            } else if ($("#"+id).val() == 1) {
                $('div[data-index="recurring_by"]').show();
                $('div[data-index="recurring_intervals"]').show();
                $('div[data-index="recurring_occurrences"]').show();
            }
        },
    });
});
