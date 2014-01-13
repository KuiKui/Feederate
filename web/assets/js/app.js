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
            $scope.activeFeed    = null;
            $scope.activeSummary = null;
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
                                $scope.loadFeeds();
                                $scope.activeFeed = feed;
                            });
                    });
            };

            $scope.loadFeeds = function () {
                Restangular
                    .all(getRoute('get_feeds'))
                    .getList()
                    .then(function(feeds) {
                        $scope.starred = feeds[0];
                        $scope.feeds = feeds.slice(1);
                    })
            };

            $scope.isActiveFeed = function (feed) {
                return angular.equals(feed, $scope.activeFeed);
            };

            $scope.loadSummaries = function (feed) {
                Restangular
                    .one(getRoute('get_feeds'), feed.id)
                    .getList('summaries')
                    .then(function(summaries) {
                        $scope.summaries     = summaries;
                        $scope.activeFeed    = feed;
                        $scope.activeSummary = null;

                        $scope.loadEntries(feed);
                    })
            };

            $scope.loadStarred = function () {
                Restangular
                    .all(getRoute('get_summaries'))
                    .getList({type: 'starred'})
                    .then(function(summaries) {
                        $scope.summaries     = summaries;
                        $scope.activeFeed    = $scope.starred;
                        $scope.activeSummary = null;

                        $scope.loadEntries($scope.starred);
                    });
            };

            $scope.isActiveSummary = function (summary) {
                return angular.equals(summary, $scope.activeSummary);
            };

            $scope.loadEntries = function (feed) {
                var entries;

                if (angular.equals(feed, $scope.starred)) {
                    entries = Restangular
                        .all(getRoute('get_entries'))
                        .getList({type: 'starred'});
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
                    $scope.activeFeed.unread_count--;
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

            $scope.loadFeeds();
        });
    });

    var getRoute = function(routeName, routeParams) {
        if (routeParams === 'undefined') {
            var routeParams = {};
        }

        return Routing.generate(routeName, routeParams, false).slice(1);
    }
})();

