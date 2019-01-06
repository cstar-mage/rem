/**
 * This script is a Pop-up content on site
 */
define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
    ],
    function ($, Component) {
        'use strict';
        // Get data from phtml file
        var mageJsComponent = function (data) {
            var getData = JSON.stringify(data);
            var jsongetData = JSON.parse(getData);
            var height_value = jsongetData["height"];
            var width_value = jsongetData["width"];
            var coockie_value = jsongetData["coockieexpire"];
            var coockie_value = coockie_value*3600;
            $(document).ready(
                function ($) {
                console.log("ageverification.phtml : document loaded");
                });
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                // buttons: [{}],
                responsiveClass: 'ageverification-poup'
            };
            $(document).keyup(function (e) {
                if (e.keyCode == 27) {
                    return false;
                }
            });
            // start : pop-up  age verification content
            $(document).ready(function () {
                $(document).mousedown(function (e) {
                    return true;
                });
                // set cookie condition below
                if ($.cookie('status') != 1) {
                    var ageconf = document.getElementsByClassName('ageconf');
                    if (ageconf.length > 0) {
                        if (document.getElementById("popup").classList.contains('ageconf')) {
                            $(document).mousedown(function (e) {
                                return false;
                            });
                        }
                    }
                    //set height and width of popup model
                    if (width_value >= 500) {
                        $(".age-pop-up").css("width", width_value);
                    } else if (width_value == "auto") {
                        $(".age-pop-up").css("width", "auto");
                    } else {
                        $(".age-pop-up").css("width", "500px");
                    }
                    if (height_value >= 300) {
                        $(".age-pop-up").css("height", height_value);
                    } else if (height_value == "auto") {
                        $(".age-pop-up").css("height", "auto");
                    } else {
                        $(".age-pop-up").css("height", "300px");
                    }
                    //show popup here
                    var mdl = jQuery('.age-pop-up').modal(options);
                    jQuery('.age-pop-up').modal('openModal');
                    // Click on enter button
                    $('#enter').click(function () {
                        if (coockie_value == "") {
                            coockie_value = 1;
                        }
                        $.cookie('status', '1', {
                            expires: +coockie_value
                        });
                        jQuery('.age-pop-up').modal('closeModal');
                        window.location.reload();
                        // detect safari browser
                        if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0) {
                            window.location.reload();
                        }
                    });
                    // Click on no button
                    $('#no').click(function () {
                        if (coockie_value == "") {
                            coockie_value = 1;
                        }
                        $.cookie('status', '0', {
                            expires: +coockie_value
                        });
                        var mdl = jQuery('.not-verify').modal();
                        jQuery('.not-verify').modal('openModal');
                        jQuery('.age-pop-up').modal('closeModal');
                        jQuery('.age-pop-up.not-verify').show();
                    });
                }
            });
            // end : pop-up  age verification content
        };
        return mageJsComponent;
    });
