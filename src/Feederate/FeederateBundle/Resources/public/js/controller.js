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
            active: null,
            error: null,
            arePosting : false
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
            Feeds.arePosting = true;
            Restangular.all(Router.get('get_feeds')).post({ title: url, url: url, targetUrl: url })
                .then(function (feed) {
                    if (callback !== undefined) {
                        callback(feed);
                    }
                    Feeds.arePosting = false;
                }, function(response) {
                    Feeds.error = JSON.parse(response.data);
                    Feeds.arePosting = false;
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

        Feeds.isStarred = function (feed) {
            return angular.equals(feed, Feeds.starred);
        };

        Feeds.type = function (feed) {
            if (angular.equals(feed, Feeds.starred)) {
                return 'starred';
            } else if (angular.equals(feed, Feeds.unread)) {
                return 'unread';
            }

            return '';
        };

        return Feeds;
    });

    feederate.factory('Entries', function (Restangular, Router, Feeds, $filter) {
        var Entries = {
            entriesList: {},
            summariesList: {},
            daysList: [],
            activeEntry: null,
            activeSummary: null,
            noMore: false,
            areLoading: false,
            page: 0
        };

        Entries.reset = function () {
            Entries.summariesList = {};
            Entries.daysList      = [];
            Entries.activeSummary = null;
            Entries.activeEntry   = null;
            Entries.noMore        = false;
            Entries.page          = 0;
            Feeds.active          = null;
        };

        Entries.loadSummaries = function (feed, callback) {

            if (!angular.equals(Feeds.active, feed)) {
                Entries.reset();
                Feeds.active = feed;
            }

            // If the load is in progress or if there are no more pages
            if (Entries.areLoading || Entries.noMore) {
                return;
            }

            var request = null,
                type    = Feeds.type(feed);

            Entries.areLoading = true;

            if (type === 'unread' || type === 'starred') {
                request = Restangular.all(Router.get('get_summaries')).getList({
                    type: type,
                    page: Entries.page + 1
                });
            } else {
                request = Restangular.one(Router.get('get_feeds'), feed.id).getList('summaries', {
                    page: Entries.page + 1
                });
            }

            request.then(function (summaries) {
                Entries.areLoading = false;

                // Last page ?
                if (summaries.length == 0) {
                    Entries.noMore = true;

                    return;
                }

                angular.forEach(summaries, function (summary) {
                    var day = $filter('formatDate')(summary.generated_at, 'YYYY-MM-DD');
                    if (Entries.daysList.indexOf(day) === -1) {
                        Entries.daysList.push(day);
                        Entries.summariesList[day] = [];
                    }

                    Entries.summariesList[day].push(summary);
                });

                Entries.page++;

                if (callback !== undefined) {
                    callback();
                }
            });
        };

        Entries.loadEntries = function (feed) {
            var request = null,
                type    = Feeds.type(feed);

            if (type === 'unread' || type === 'starred') {
                request = Restangular.all(Router.get('get_entries')).getList({
                    type: type,
                    page: Entries.page
                });
            } else {
                request = Restangular.one(Router.get('get_feeds'), feed.id).getList('entries', {
                    page: Entries.page
                });
            }

            request.then(function (entries) {
                angular.forEach(entries, function (entry) {
                    Entries.entriesList[entry.id] = entry;
                });
            });
        };

        Entries.markAsRead = function (summary, canToggled) {
            canToggled = typeof canToggled !== 'undefined' ? canToggled : false;

            if (canToggled || (!canToggled && !summary.is_read)) {
                Restangular
                    .oneUrl(Router.get('post_summary_summaries_read', {id: summary.id}))
                    .customPOST({is_read: !summary.is_read});

                summary.is_read = !summary.is_read;

                if (summary.is_read) {
                    Feeds.list[summary.feed_id].unread_count--;
                    Feeds.unread.unread_count--;
                } else {
                    Feeds.list[summary.feed_id].unread_count++;
                    Feeds.unread.unread_count++;
                }
            }
        };

        Entries.markAsStarred = function (summary) {
            Restangular
                .oneUrl(Router.get('post_summary_summaries_star', {id: summary.id}))
                .customPOST({is_starred: !summary.is_starred});

            summary.is_starred = !summary.is_starred;

            if (summary.is_starred) {
                Feeds.starred.unread_count++;
            } else {
                Feeds.starred.unread_count--;
            }
        };

        Entries.loadIsBusy = function () {
            return Entries.noMore || Entries.areLoading;
        };

        Entries.isActiveSummary = function (summary) {
            return angular.equals(summary, Entries.activeSummary);
        };

        Entries.setActive = function(summary) {
            Entries.activeSummary = summary;

            // Replace relative img path
            Entries.entriesList[summary.id].content = Entries
                .entriesList[summary.id]
                .content
                .replace(/(<img[^>]+src=["'])(\/[^"']+)/gi, '$1' + Feeds.list[summary.feed_id].target_url.replace(/\/$/, '') + '$2');

            Entries.activeEntry = Entries.entriesList[summary.id];
        }

        return Entries;
    });



    angular
        .module('feederate')
        .controller('BoardCtrl', function BoardCtrl ($scope, Router, Feeds, Entries, Restangular, $location, $anchorScroll) {

        angular.element(document).ready(function () {
            $scope.user         = null;
            $scope.Feeds        = Feeds;
            $scope.Entries      = Entries;
            $scope.selectedType = 'summaries';

            $scope.loadFeeds = function (callback) {
                Feeds.load(callback);
            };

            $scope.addFeed = function () {
                Feeds.add($scope.newFeedUrl, function (feed) {
                    $scope.newFeedUrl = '';
                    Feeds.load(function () {
                        $scope.loadSummaries(Feeds.list[feed.id]);

                        // Auto scroll into active feed
                        $location.hash('feed_' + feed.id);
                        $anchorScroll();
                    });
                });
            };

            $scope.deleteFeed = function (feed) {
                if (confirm('Do you really want delete feed "' + feed.title + '" ?')) {
                    Feeds.delete(feed, function () {
                        Feeds.load(function () {
                            Entries.reset();
                            $location.path('feeds');
                        })
                    });
                }
            };

            $scope.markFeedAsRead = function (feed) {
                if (confirm('Do you really want mark all entries of "' + feed.title + '" as read ?')) {
                    Feeds.markAsRead(feed, function () {
                        if (!Feeds.isStarred(feed)) {
                            if (!Feeds.isUnread(feed)) {
                                Feeds.unread.unread_count       -= Feeds.list[feed.id].unread_count;
                                Feeds.list[feed.id].unread_count = 0;

                                // If read feeds is hidden, reset active
                                if ($scope.user.is_read_feeds_hidden) {
                                    Entries.reset();
                                }
                            } else {
                                // All unread_count must be null
                                Feeds.unread.unread_count = 0;
                                angular.forEach(Feeds.list, function (feed) {
                                    feed.unread_count = 0;
                                });
                            }

                            angular.forEach(Entries.daysList, function (day) {
                                angular.forEach(Entries.summariesList[day], function (summary) {
                                    summary.is_read = true;
                                });
                            });
                        } else {
                            // We reload feeds because it's too complex to manage unread_count
                            Feeds.load(function () {
                                $scope.loadSummaries(Feeds.starred);

                                // Auto scroll into active feed
                                $location.hash('feed_' + feed.id);
                                $anchorScroll();
                            });
                        }

                        $location.path('feeds');
                    });
                }
            };

            $scope.loadSummaries = function (feed) {
                Entries.loadSummaries(feed, function () {
                    $scope.loadEntries(feed);
                });
            };

            $scope.loadEntries = function (feed) {
                Entries.loadEntries(feed);
            };

            $scope.loadEntry = function (summary) {
                Entries.setActive(summary);
            };

            $scope.markAsRead = function (summary, canToggled) {
                Entries.markAsRead(summary, canToggled)
            };

            $scope.markAsStarred = function (summary) {
                Entries.markAsStarred(summary);
            };

            $scope.toggleReadFeeds = function () {
                $scope.user.is_read_feeds_hidden = !$scope.user.is_read_feeds_hidden;

                Restangular
                    .one(Router.get('get_user'))
                    .customPOST({is_read_feeds_hidden: $scope.user.is_read_feeds_hidden});
            }

            $scope.getShownFeeds = function () {
                if (!$scope.user || !$scope.user.is_read_feeds_hidden) {
                    return Feeds.list;
                } else {
                    var shownFeeds = {}
                    angular.forEach(Feeds.list, function(feed) {
                        if (feed.unread_count !== 0) {
                            shownFeeds[feed.id] = feed;
                        }
                    });

                    return shownFeeds;
                }
            }

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

            Restangular
                .one(Router.get('get_user'))
                .get()
                .then(function(user) {
                    $scope.user = user;
                    $scope.loadFeeds(function () {
                        if (Feeds.unread.unread_count > 0) {
                            $scope.loadSummaries(Feeds.unread);
                        } else {
                            $location.path('feeds');
                        }
                    });
                });
        });
    });
})();

