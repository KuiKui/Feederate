(function () {
    'use strict';

    angular.module('truncate', [])
        .filter('characters', function () {
            return function (input, chars, breakOnWord) {
                if (isNaN(chars)) return input;
                if (chars <= 0) return '';
                if (input && input.length >= chars) {
                    input = input.substring(0, chars);

                    if (!breakOnWord) {
                        var lastspace = input.lastIndexOf(' ');
                        //get last space
                        if (lastspace !== -1) {
                            input = input.substr(0, lastspace);
                        }
                    }else{
                        while(input.charAt(input.length-1) == ' '){
                            input = input.substr(0, input.length -1);
                        }
                    }
                    return input + '...';
                }
                return input;
            };
        })
        .filter('words', function () {
            return function (input, words) {
                if (isNaN(words)) return input;
                if (words <= 0) return '';
                if (input) {
                    var inputWords = input.split(/\s+/);
                    if (inputWords.length > words) {
                        input = inputWords.slice(0, words).join(' ') + '...';
                    }
                }
                return input;
            };
        });

    var app = angular.module('feederate', ['ngSanitize', 'truncate', 'restangular']);

    app.filter('objectToArray', function() {
        return function(obj) {
            if (!(obj instanceof Object)) return obj;
            return _.map(obj, function(val, key) {
                return Object.defineProperty(val, '$key', {__proto__: null, value: key});
            });
        }
    });

    app.filter('stringToDate', function() {
        return function(string) {
            return new Date(string);
        }
    });

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

    app.controller('BoardCtrl', function BoardCtrl ($scope, Restangular) {
        angular.element(document).ready(function () {
            $scope.starred       = null;
            $scope.unread        = null;
            $scope.activeFeed    = null;
            $scope.activeSummary = null;
            $scope.feeds         = {};
            $scope.entries       = [];

            $scope.addFeed = function () {
                Restangular
                    .all(getRoute('get_feeds'))
                    .post({
                        title: $scope.newFeedUrl,
                        url: $scope.newFeedUrl,
                        targetUrl: $scope.newFeedUrl
                    })
                    .then(function (feed) {
                        feed
                            .customGET('parse')
                            .then(function() {
                                $scope.newFeedUrl = '';
                                $scope.loadFeeds(function() {
                                    $scope.loadSummaries($scope.feeds[feed.id]);
                                });
                            });
                    });
            };

            $scope.loadFeeds = function (callback) {
                Restangular
                    .all(getRoute('get_feeds'))
                    .getList()
                    .then(function(feeds) {
                        $scope.unread  = feeds[0];
                        $scope.starred = feeds[1];

                        angular.forEach(feeds.slice(2), function(feed) {
                            $scope.feeds[feed.id] = feed;
                        });

                        callback();
                    })
            };

            $scope.isActiveFeed = function (feed) {
                return angular.equals(feed, $scope.activeFeed);
            };

            $scope.loadSummaries = function (feed) {
                var summaries, type;

                if (type = getFeedType(feed)) {
                    summaries = Restangular
                        .all(getRoute('get_summaries'))
                        .getList({type: type});
                } else {
                    summaries = Restangular
                        .one(getRoute('get_feeds'), feed.id)
                        .getList('summaries');
                }

                summaries.then(function(summaries) {
                    $scope.summaries     = summaries;
                    $scope.activeFeed    = feed;
                    $scope.activeSummary = null;

                    $scope.loadEntries(feed);
                }); 
            };

            $scope.isActiveSummary = function (summary) {
                return angular.equals(summary, $scope.activeSummary);
            };

            $scope.loadEntries = function (feed) {
                var entries, type;

                if (type = getFeedType(feed)) {
                    entries = Restangular
                        .all(getRoute('get_entries'))
                        .getList({type: type});
                } else {
                    entries = Restangular
                        .one(getRoute('get_feeds'), feed.id)
                        .getList('entries');
                }

                entries.then(function(entries) {
                    angular.forEach(entries, function(entry) {
                        $scope.entries[entry.id] = entry;
                    });
                });
            };

            $scope.loadEntry = function (summary) {
                $scope.entry         = $scope.entries[summary.id];
                $scope.activeSummary = summary;
            };

            $scope.markAsRead = function (summary) {
                if (!summary.is_read) {
                    Restangular
                        .oneUrl(getRoute('post_summary_summaries_read', {id: summary.id}))
                        .customPOST({is_read: true});

                    summary.is_read = true;
                    $scope.feeds[summary.feed_id].unread_count--;
                    $scope.unread.unread_count--;
                }
            };

            $scope.markAsStarred = function (summary) {
                Restangular
                    .oneUrl(getRoute('post_summary_summaries_star', {id: summary.id}))
                    .customPOST({is_starred: !summary.is_starred});

                summary.is_starred = !summary.is_starred;

                if (summary.is_starred) {
                    $scope.starred.unread_count++;
                } else {
                    $scope.starred.unread_count--;
                }
            };

            var getFeedType = function(feed) {
                if (angular.equals(feed, $scope.starred)) {
                    return 'starred';
                } else if (angular.equals(feed, $scope.unread)) {
                    return 'unread';
                } else {
                    return '';
                }
            }

            var getRoute = function(routeName, routeParams) {
                if (routeParams === 'undefined') {
                    var routeParams = {};
                }

                return Routing.generate(routeName, routeParams, false).slice(1);
            }

            $scope.sortMe = function() {
                return function(object) {
                    return object.title;
                }
            }

            $scope.loadFeeds(function() {
                $scope.loadSummaries($scope.unread);
            });
        });
    });
})();

