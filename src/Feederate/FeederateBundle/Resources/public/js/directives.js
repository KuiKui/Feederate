(function () {
    'use strict';

    var app = angular.module('feederate');

    app.directive('ngEnter', function() {
        return function(scope, element, attrs) {
            element.bind("keydown keypress", function(event) {
                if(event.which === 13) {
                    scope.$apply(function(){
                        scope.$eval(attrs.ngEnter);
                    });

                    event.preventDefault();
                }
            });
        };
    });

    app.directive('entryContentBind',  ['$sce', '$parse', function($sce, $parse) {
        return function(scope, element, attr) {
            element.addClass('ng-binding').data('$binding', attr.entryContentBind);

            var parsed = $parse(attr.entryContentBind);
            function getStringValue() { return (parsed(scope) || '').toString(); }

            scope.$watch(getStringValue, function ngBindHtmlWatchAction(value) {
                element.html($sce.getTrustedHtml(parsed(scope)) || '');

                // reset scroll entry
                if (scope.oneColumn) {
                    $(window).scrollTop(0);
                } else {
                    element.parents('#entry .entry-content').scrollTop(0);
                }
            });
        };
    }]);
})();