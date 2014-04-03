angular.module('infinite-scroll', [])
    .directive('infiniteScroll', ['$rootScope', '$window', '$timeout', function($rootScope, $window, $timeout) {
        return {
            link: function(scope, elem, attrs) {
                var checkWhenEnabled, handler, scrollDistance, scrollEnabled;
                $window = angular.element($window);
                scrollDistance = 0;

                if (attrs.infiniteScrollDistance != null) {
                    scope.$watch(attrs.infiniteScrollDistance, function(value) {
                        return scrollDistance = value;
                    });
                }

                scrollEnabled = true;
                checkWhenEnabled = false;

                if (attrs.infiniteScrollDisabled != null) {
                    scope.$watch(attrs.infiniteScrollDisabled, function(value) {
                        scrollEnabled = !value;
                        if (scrollEnabled && checkWhenEnabled) {
                            checkWhenEnabled = false;
                            return handler();
                        }
                    });
                }

                $rootScope.$on('refreshStart', function(event, parameters){
                    elem.animate({ scrollTop: "0" });
                });

                handler = function() {
                    var container, elementBottom, remaining, shouldScroll, containerBottom;

                    container       = $(elem.children()[0]);
                    remaining       = elem.scrollTop() + elem.height() ;
                    shouldScroll    = remaining + elem.height() * scrollDistance >= container.height();

                    if (shouldScroll && scrollEnabled) {
                        if ($rootScope.$$phase) {
                            return scope.$eval(attrs.infiniteScroll);
                        } else {
                            return scope.$apply(attrs.infiniteScroll);
                        }
                    } else if (shouldScroll) {
                        return checkWhenEnabled = true;
                    }
                };

                if (elem.prop('tagName') === 'BODY') {
                    $(window).on('scroll', handler);
                } else {
                    elem.on('scroll', handler);
                }

                scope.$on('$destroy', function() {
                  return $window.off('scroll', handler);
                });

                return $timeout((function() {
                    if (attrs.infiniteScrollImmediateCheck) {
                        if (scope.$eval(attrs.infiniteScrollImmediateCheck)) {
                            return handler();
                        }
                    } else {
                        return handler();
                    }
                }), 0);
            }
        };
    }]);