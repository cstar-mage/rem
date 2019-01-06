/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            jqueryfunction:     'FME_Events/js/jqueryfunction',
            jqueryfunctionb:    'FME_Events/js/jqueryfunctionb',
            shadowbox:          'FME_Events/js/shadowbox',
            excanvas:           'FME_Events/js/excanvas',
            opentip:            'FME_Events/js/opentip',
            moment:             'FME_Events/js/moment.min',
            fullcalendar:       'FME_Events/js/fullcalendar',
            fancybox:           'FME_Events/js/jquery.fancybox'
             
                        
        }
    },
    paths: {
        'jqueryfunction':      'FME_Events/js/jqueryfunction',
        'shadowbox':           'FME_Events/js/shadowbox',
        'jqueryfunctionb':     'FME_Events/js/jqueryfunctionb',
        'excanvas':            'FME_Events/js/excanvas',
        'opentip':             'FME_Events/js/opentip',
        'moment':              'FME_Events/js/moment.min',
        'fullcalendar':        'FME_Events/js/fullcalendar',
        'fancybox':            'FME_Events/js/jquery.fancybox'
          
    },
  
    shim: {
        
        
        "shadowbox": {
            deps: ["jquery"]
        },
        "jqueryfunction": {
            deps: ["owlcarousel"]
        },
        "jqueryfunction": {
            deps: ["jquery"]
        },
        "owlcarousel": {
            deps: ["jquery"]
        },
        "excanvas":{
            deps: ["jquery"]
        },
        "opentip":{
            deps: ["jquery"]
        },
        "moment":{
            deps: ["jquery"]
        },
        "fullcalendar":{
            deps: ["moment"]
        },
        shim: {
            'fancybox': {
                deps: ['jquery']
            }
        }
        
        
    }

};
