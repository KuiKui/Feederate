(function () {
    'use strict';

    angular
        .module('feederate')
        .controller('BoardCtrl', function BoardCtrl ($scope, $filter, Restangular, $location, $anchorScroll) {

        angular.element(document).ready(function () {
            $scope.starred             = null;
            $scope.unread              = null;
            $scope.activeFeed          = null;
            $scope.activeSummary       = null;
            $scope.focusArea           = null;
            $scope.user                = null;
            $scope.summariesAreLoading = false;
            $scope.noMoreSummary       = false;
            $scope.currentPage         = 0;
            $scope.feeds               = {};
            $scope.entries             = [];
            $scope.summariesDays       = [];
            $scope.summaries           = {};

            $scope.addFeed = function () {
                Restangular
                    .all(getRoute('get_feeds'))
                    .post({
                        title: $scope.newFeedUrl,
                        url: $scope.newFeedUrl,
                        targetUrl: $scope.newFeedUrl
                    })
                    .then(function (feed) {
                        $scope.newFeedUrl = '';
                        $scope.loadFeeds(function() {
                            $scope.loadSummaries($scope.feeds[feed.id]);

                            // Auto scroll into active feed
                            $location.hash('feed_' + feed.id);
                            $anchorScroll();
                        });
                    });
            };

            $scope.loadFeeds = function (callback) {
                Restangular
                    .all(getRoute('get_feeds'))
                    .getList()
                    .then(function(feeds) {
                        $scope.feeds   = {};
                        $scope.unread  = feeds[0];
                        $scope.starred = feeds[1];

                        angular.forEach(feeds.slice(2), function(feed) {
                            $scope.feeds[feed.id] = feed;
                        });

                        callback();
                    });
            };

            $scope.deleteFeed = function (feed) {
                if (confirm('Do you really want delete feed "' + feed.title + '" ?')) {
                    feed
                        .remove()
                        .then(function() {
                            $scope.loadFeeds(function() {
                                $scope.loadSummaries($scope.unread);

                                // Auto scroll into active feed
                                $location.hash('feed_unread');
                                $anchorScroll();
                            });
                        });
                }
            };

            $scope.markFeedAsRead = function (feed) {
                if (confirm('Do you really want mark all entries of "' + feed.title + '" as read ?')) {
                    Restangular
                        .oneUrl(getRoute('post_feed_read', {id: feed.id}))
                        .customPOST({is_read: true})
                        .then(function() {
                            if (!$scope.isStarredFeed(feed)) {
                                if (!$scope.isUnreadFeed(feed)) {
                                    $scope.unread.unread_count -= $scope.feeds[feed.id].unread_count;
                                    $scope.feeds[feed.id].unread_count = 0;
                                } else {
                                    // All unread_count must be null
                                    $scope.unread.unread_count = 0;
                                    angular.forEach($scope.feeds, function(feed) {
                                        feed.unread_count = 0;
                                    });
                                }

                                angular.forEach($scope.summariesDays, function(day) {
                                    angular.forEach($scope.summaries[day], function(summary) {
                                        summary.is_read = true;
                                    });
                                });
                            } else {
                                // We reload feeds because it's too complex to manage unread_count
                                $scope.loadFeeds(function() {
                                    $scope.loadSummaries($scope.starred);

                                    // Auto scroll into active feed
                                    $location.hash('feed_' + feed.id);
                                    $anchorScroll();
                                });
                            }
                        });
                }
            };

            $scope.toggleReadFeeds = function () {
                $scope.user.is_read_feeds_hidden = !$scope.user.is_read_feeds_hidden;

                Restangular
                    .one(getRoute('get_user'))
                    .customPOST({is_read_feeds_hidden: $scope.user.is_read_feeds_hidden});
            }

            $scope.getShownFeeds = function () {
                if (!$scope.user || !$scope.user.is_read_feeds_hidden) {
                    return $scope.feeds;
                } else {
                    var shownFeeds = {}
                    angular.forEach($scope.feeds, function(feed) {
                        if (feed.unread_count !== 0) {
                            shownFeeds[feed.id] = feed;
                        }
                    });

                    return shownFeeds;
                }
            }

            $scope.isActiveFeed = function (feed) {
                return angular.equals(feed, $scope.activeFeed);
            };

            $scope.isUnreadFeed = function (feed) {
                return angular.equals(feed, $scope.unread);
            };

            $scope.isStarredFeed = function (feed) {
                return angular.equals(feed, $scope.starred);
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
                $scope.activeFeed          = feed;

                if (type = getFeedType(feed)) {
                    summaries = Restangular
                        .all(getRoute('get_summaries'))
                        .getList({type: type, page: $scope.currentPage + 1});
                } else {
                    summaries = Restangular
                        .one(getRoute('get_feeds'), feed.id)
                        .getList('summaries', {page: $scope.currentPage + 1});
                }

                summaries.then(function(summaries) {
                    $scope.summariesAreLoading = false;

                    if (!summaries.length) {
                        $scope.noMoreSummary = true;
                        return;
                    }

                    angular.forEach(summaries, function(summary) {
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
            }

            $scope.isActiveSummary = function (summary) {
                return angular.equals(summary, $scope.activeSummary);
            };

            $scope.loadEntries = function (feed) {
                var entries, type;

                if (type = getFeedType(feed)) {
                    entries = Restangular
                        .all(getRoute('get_entries'))
                        .getList({type: type, page: $scope.currentPage});
                } else {
                    entries = Restangular
                        .one(getRoute('get_feeds'), feed.id)
                        .getList('entries', {page: $scope.currentPage});
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

                // reset scroll entry
                $('#entry .entry-content').scrollTop(0);
            };

            $scope.markAsRead = function (summary, onlyUnread) {
                if (onlyUnread === undefined || !onlyUnread || (onlyUnread && !summary.is_read)) {
                    Restangular
                        .oneUrl(getRoute('post_summary_summaries_read', {id: summary.id}))
                        .customPOST({is_read: !summary.is_read});

                    summary.is_read = !summary.is_read;

                    if (summary.is_read) {
                        $scope.feeds[summary.feed_id].unread_count--;
                        $scope.unread.unread_count--;
                    } else {
                        $scope.feeds[summary.feed_id].unread_count++;
                        $scope.unread.unread_count++;
                    }
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
                routeParams = (routeParams === undefined ? {} : routeParams);

                return Routing.generate(routeName, routeParams, false).slice(1);
            }

            $scope.sortMe = function() {
                return function(object) {
                    return object.title;
                }
            }

            $scope.$watch(function(){
                return $location.path();
            }, function(value){
                if (value) {
                    $scope.focusArea = value.replace('/', '');
                } else {
                    $scope.focusArea = 'summaries';
                }
            });

            Restangular
                .one(getRoute('get_user'))
                .get()
                .then(function(user) {
                    $scope.user = user;
                    $scope.loadFeeds(function() {
                        $scope.loadSummaries($scope.unread);
                    });
                });
        });
    });
})();

