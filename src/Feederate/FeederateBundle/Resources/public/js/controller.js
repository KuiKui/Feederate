(function () {
    'use strict';

    var feederate = angular.module('feederate');

    /**
     * Router factory
     */
    feederate.factory('Router', function () {
        return {
            get: function (name, parameters) {
                if (parameters === undefined) {
                    parameters = {};
                }

                return Routing.generate(name, parameters, false).slice(1);
            }
        };
    });

    /**
     * Feed factory
     */
    feederate.factory('Feeds', function (Restangular, Router) {

        // defined attributes
        var Feeds = {
            list: {},
            unread: {},
            starred: {},
            active: {},
        };

        /**
         * Load feeds
         *
         * @param function callback
         */
        Feeds.load = function (callback) {
            Restangular.all(Router.get('get_feeds')).getList()
                .then(function (data) {
                    Feeds.list    = {};
                    Feeds.unread  = data[0];
                    Feeds.starred = data[1];

                    angular.forEach(data.slice(2), function (feed) {
                        Feeds.list[feed.id] = feed;
                    });

                    if (callback !== undefined) {
                        callback();
                    }
                });
        };

        Feeds.add = function (url, callback) {
            Restangular.all(Router.get('get_feeds')).post({ title: url, url: url, targetUrl: url })
                .then(function () {
                    if (callback !== undefined) {
                        callback();
                    }
                });
        };

        Feeds.delete = function (feed, callback) {
            feed.remove()
                .then(function () {
                    if (callback !== undefined) {
                        callback();
                    }
                });
        };

        Feeds.markAsRead = function (feed, callback) {
            Restangular.oneUrl(Router.get('post_feed_read', {id: feed.id})).customPOST({is_read: true})
                .then(function () {
                    if (callback !== undefined) {
                        callback();
                    }
                });
        };

        Feeds.isActive = function (feed) {
            return angular.equals(feed, Feeds.active);
        };

        Feeds.isUnread = function (feed) {
            return angular.equals(feed, Feeds.unread);
        };

        return Feeds;
    });



    angular
        .module('feederate')
        .controller('BoardCtrl', function BoardCtrl ($scope, $filter, Router, Feeds, Restangular, $location, $anchorScroll) {

        angular.element(document).ready(function () {
            $scope.activeSummary       = null;
            $scope.selectedType        = 'summaries';
            $scope.summariesAreLoading = false;
            $scope.noMoreSummary       = false;
            $scope.currentPage         = 0;
            $scope.entries             = [];
            $scope.summariesDays       = [];
            $scope.summaries           = {};

            $scope.Feeds = Feeds;

            $scope.loadFeeds = function (callback) {
                $scope.Feeds.load(callback);
            };

            $scope.addFeed = function () {
                $scope.Feeds.add($scope.newFeedUrl, function (feed) {
                    $scope.newFeedUrl = '';
                    $scope.Feeds.load(function () {
                        $scope.loadSummaries($scope.Feeds.list[feed.id]);

                        // Auto scroll into active feed
                        $location.hash('feed_' + feed.id);
                        $anchorScroll();
                    });
                });
            };

            $scope.deleteFeed = function (feed) {
                if (confirm('Do you really want delete feed "' + feed.title + '" ?')) {
                    $scope.Feeds.delete(feed, function () {
                        $scope.Feeds.load(function () {
                            $scope.loadSummaries($scope.Feeds.unread);

                            // Auto scroll into active feed
                            $location.hash('feed_unread');
                            $anchorScroll();
                        })
                    });
                }
            };

            $scope.markFeedAsRead = function (feed) {
                if (confirm('Do you really want mark all entries of "' + feed.title + '" as read ?')) {
                    $scope.Feeds.markAsRead(feed, function () {
                        if (!$scope.Feeds.isStarred(feed)) {
                            if (!$scope.Feeds.isUnread(feed)) {
                                $scope.Feeds.unread.unread_count       -= $scope.Feeds.list[feed.id].unread_count;
                                $scope.Feeds.list[feed.id].unread_count = 0;
                            } else {

                                // All unread_count must be null
                                $scope.Feeds.unread.unread_count = 0;
                                angular.forEach($scope.Feeds.list, function (feed) {
                                    feed.unread_count = 0;
                                });
                            }

                            angular.forEach($scope.summariesDays, function (day) {
                                angular.forEach($scope.summaries[day], function (summary) {
                                    summary.is_read = true;
                                });
                            });
                        } else {

                            // We reload feeds because it's too complex to manage unread_count
                            $scope.Feeds.load(function () {
                                $scope.loadSummaries($scope.Feeds.starred);

                                // Auto scroll into active feed
                                $location.hash('feed_' + feed.id);
                                $anchorScroll();
                            });
                        }
                    });
                }
            };

            $scope.loadSummaries = function (feed, resetFeed) {
                var summaries, type;

                if (resetFeed = (resetFeed === undefined ? true : resetFeed)) {
                    $scope.summaries     = {};
                    $scope.summariesDays = [];
                    $scope.currentPage   = 0;
                    $scope.noMoreSummary = false;
                    $scope.activeSummary = null;
                }

                if ($scope.summariesAreLoading || $scope.noMoreSummary) {
                    return;
                }

                $scope.summariesAreLoading = true;
                $scope.Feeds.active        = feed;

                if (type = getFeedType(feed)) {
                    summaries = Restangular
                        .all(Router.get('get_summaries'))
                        .getList({type: type, page: $scope.currentPage + 1});
                } else {
                    summaries = Restangular
                        .one(Router.get('get_feeds'), feed.id)
                        .getList('summaries', {page: $scope.currentPage + 1});
                }

                summaries.then(function (summaries) {
                    $scope.summariesAreLoading = false;

                    if (!summaries.length) {
                        $scope.noMoreSummary = true;
                        return;
                    }

                    angular.forEach(summaries, function (summary) {
                        var day = $filter('formatDate')(summary.generated_at, 'YYYY-MM-DD');
                        if ($scope.summariesDays.indexOf(day) === -1) {
                            $scope.summariesDays.push(day);
                            $scope.summaries[day] = [];
                        }
                        $scope.summaries[day].push(summary);
                    });

                    $scope.currentPage++;
                    $scope.loadEntries(feed);
                });
            };

            $scope.summaryLoadIsBusy = function () {
                return $scope.noMoreSummary || $scope.summariesAreLoading;
            };

            $scope.isActiveSummary = function (summary) {
                return angular.equals(summary, $scope.activeSummary);
            };

            $scope.loadEntries = function (feed) {
                var entries, type;

                if (type = getFeedType(feed)) {
                    entries = Restangular
                        .all(Router.get('get_entries'))
                        .getList({type: type, page: $scope.currentPage});
                } else {
                    entries = Restangular
                        .one(Router.get('get_feeds'), feed.id)
                        .getList('entries', {page: $scope.currentPage});
                }

                entries.then(function (entries) {
                    angular.forEach(entries, function (entry) {
                        $scope.entries[entry.id] = entry;
                    });
                });
            };

            $scope.loadEntry = function (summary) {
                $scope.entry         = $scope.entries[summary.id];
                $scope.activeSummary = summary;

                // reset scroll entry
                $('#entry .entry-content').scrollTop(0);
            };

            $scope.markAsRead = function (summary, onlyUnread) {
                if (onlyUnread === undefined || !onlyUnread || (onlyUnread && !summary.is_read)) {
                    Restangular
                        .oneUrl(Router.get('post_summary_summaries_read', {id: summary.id}))
                        .customPOST({is_read: !summary.is_read});

                    summary.is_read = !summary.is_read;

                    if (summary.is_read) {
                        $scope.Feeds.list[summary.feed_id].unread_count--;
                        $scope.Feeds.unread.unread_count--;
                    } else {
                        $scope.Feeds.list[summary.feed_id].unread_count++;
                        $scope.Feeds.unread.unread_count++;
                    }
                }
            };

            $scope.markAsStarred = function (summary) {
                Restangular
                    .oneUrl(Router.get('post_summary_summaries_star', {id: summary.id}))
                    .customPOST({is_starred: !summary.is_starred});

                summary.is_starred = !summary.is_starred;

                if (summary.is_starred) {
                    $scope.Feeds.starred.unread_count++;
                } else {
                    $scope.Feeds.starred.unread_count--;
                }
            };

            var getFeedType = function (feed) {
                if (angular.equals(feed, $scope.Feeds.starred)) {
                    return 'starred';
                } else if (angular.equals(feed, $scope.Feeds.unread)) {
                    return 'unread';
                } else {
                    return '';
                }
            };

            $scope.sortMe = function () {
                return function (object) {
                    return object.title;
                }
            };

            $scope.$watch(function (){
                return $location.path();
            }, function (value){
                var splitUrl = value.replace(/^\/+|\/+$/g,'').split('/');

                if (splitUrl[0]) {
                    $scope.selectedType = splitUrl[0];
                }

                if (splitUrl[1]) {
                    $scope.selectedTypeId = splitUrl[1];
                }
            });

            $scope.loadFeeds(function () {
                $scope.loadSummaries($scope.Feeds.unread);
            });
        });
    });
})();

