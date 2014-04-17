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


    var app = angular.module('feederate', ['ngSanitize', 'truncate', 'restangular', 'infinite-scroll', 'jmdobry.angular-cache']);

    app.run(function ($http, $angularCacheFactory) {
        $angularCacheFactory('defaultCache', {
            maxAge: 300000, // Items added to this cache expire after 5 minutes.
            deleteOnExpire: 'aggressive', // Items will be deleted from this cache right when they expire.
            recycleFreq: 10000, // Check for expired items every 10 seconds.
            storageMode: 'localStorage' // This cache will sync itself with `localStorage`.
        });

        $http.defaults.cache = $angularCacheFactory.get('defaultCache');
    });

    app.config(function(RestangularProvider) {
        RestangularProvider.setDefaultHeaders({'Content-Type': 'application/json'});
        RestangularProvider.setDefaultHttpFields({cache: true});
    });
})();