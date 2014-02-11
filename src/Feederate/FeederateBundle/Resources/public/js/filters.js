(function () {
    'use strict';

    var app = angular.module('feederate');

    app.filter('objectToArray', function() {
        return function(obj) {
            if (!(obj instanceof Object)) return obj;
            return _.map(obj, function(val, key) {
                return Object.defineProperty(val, '$key', {__proto__: null, value: key});
            });
        }
    });

    app.filter('arrayReverse', function() {
        return function(array) {
            if (array) {
                return array.slice().reverse();
            }
        };
    });

    app.filter('formatDate', function() {
        return function(string, format) {
            var date;

            if (format == 'short') {
                date = moment(string).fromNow();

                if (date.indexOf('long') !== -1) {
                    var year = '';

                    if (moment(string).format('YYYY') !== moment().format('YYYY')) {
                        year = ' YYYY';
                    }

                    date = moment(string).format('D MMM' + year);
                }
            } else {
                date = moment(string).format(format);
            }

            return date;
        }
    });
})();