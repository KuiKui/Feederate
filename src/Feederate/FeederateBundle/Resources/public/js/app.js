(function () {
    'use strict';

    moment.lang('shortEn', {
        relativeTime : {
            past:   "%s",
            s:  "just now",
            m:  "1min",
            mm: "%dmin",
            h:  "1h",
            hh: "%dh",
            d:  "yesterday",
            dd: "long",
            M:  "long",
            MM: "long",
            y:  "long",
            yy: "long"
        }
    });


    angular.module('feederate', ['ngSanitize', 'truncate', 'restangular', 'infinite-scroll']);
})();