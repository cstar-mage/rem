/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';
        var tempShippingFeeData = window.mageworxShippingFeeInfo;
        var allData = ko.observable(tempShippingFeeData);

        return {
            allData: allData,
            getData: function () {
                return allData;
            },

            setData: function (data) {
                allData(data);
            },

            validate: function () {
                //if (this.allData().is_enable) {
                //return this.allData().is_valid;
                //}
                return true;
            }
        }
    }
);

