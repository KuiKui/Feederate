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

    feederate.factory('keyboardManager', ['$window', '$timeout', function ($window, $timeout) {
        var keyboardManagerService = {};

        var defaultOpt = {
            'type':             'keydown',
            'propagate':        false,
            'inputDisabled':    false,
            'target':           $window.document,
            'keyCode':          false
        };
        // Store all keyboard combination shortcuts
        keyboardManagerService.keyboardEvent = {}
        // Add a new keyboard combination shortcut
        keyboardManagerService.bind = function (label, callback, opt) {
            var fct, elt, code, k;
            // Initialize opt object
            opt   = angular.extend({}, defaultOpt, opt);
            label = label.toLowerCase();
            elt   = opt.target;
            if(typeof opt.target == 'string') elt = document.getElementById(opt.target);

            fct = function (e) {
                e = e || $window.event;

                // Disable event handler when focus input and textarea
                if (opt['inputDisabled']) {
                    var elt;
                    if (e.target) elt = e.target;
                    else if (e.srcElement) elt = e.srcElement;
                    if (elt.nodeType == 3) elt = elt.parentNode;
                    if (elt.tagName == 'INPUT' || elt.tagName == 'TEXTAREA') return;
                }

                // Find out which key is pressed
                if (e.keyCode) code = e.keyCode;
                else if (e.which) code = e.which;
                var character = String.fromCharCode(code).toLowerCase();

                if (code == 188) character = ","; // If the user presses , when the type is onkeydown
                if (code == 190) character = "."; // If the user presses , when the type is onkeydown

                var keys = label.split("+");
                // Key Pressed - counts the number of valid keypresses - if it is same as the number of keys, the shortcut function is invoked
                var kp = 0;
                // Work around for stupid Shift key bug created by using lowercase - as a result the shift+num combination was broken
                var shift_nums = {
                    "`":"~",
                    "1":"!",
                    "2":"@",
                    "3":"#",
                    "4":"$",
                    "5":"%",
                    "6":"^",
                    "7":"&",
                    "8":"*",
                    "9":"(",
                    "0":")",
                    "-":"_",
                    "=":"+",
                    ";":":",
                    "'":"\"",
                    ",":"<",
                    ".":">",
                    "/":"?",
                    "\\":"|"
                };
                // Special Keys - and their codes
                var special_keys = {
                    'esc':27,
                    'escape':27,
                    'tab':9,                
                    'space':32,
                    'return':13,
                    'enter':13,
                    'backspace':8,

                    'scrolllock':145,
                    'scroll_lock':145,
                    'scroll':145,
                    'capslock':20,
                    'caps_lock':20,
                    'caps':20,
                    'numlock':144,
                    'num_lock':144,
                    'num':144,

                    'pause':19,
                    'break':19,

                    'insert':45,
                    'home':36,
                    'delete':46,
                    'end':35,

                    'pageup':33,
                    'page_up':33,
                    'pu':33,

                    'pagedown':34,
                    'page_down':34,
                    'pd':34,

                    'left':37,
                    'up':38,
                    'right':39,
                    'down':40,

                    'f1':112,
                    'f2':113,
                    'f3':114,
                    'f4':115,
                    'f5':116,
                    'f6':117,
                    'f7':118,
                    'f8':119,
                    'f9':120,
                    'f10':121,
                    'f11':122,
                    'f12':123
                };
                // Some modifiers key
                var modifiers = {
                    shift: {
                        wanted:     false, 
                        pressed:    e.shiftKey ? true : false
                    },
                    ctrl : {
                        wanted:     false, 
                        pressed:    e.ctrlKey ? true : false
                    },
                    alt  : {
                        wanted:     false, 
                        pressed:    e.altKey ? true : false
                    },
                    meta : { //Meta is Mac specific
                        wanted:     false, 
                        pressed:    e.metaKey ? true : false
                    }
                };
                // Foreach keys in label (split on +)
                for(var i=0, l=keys.length; k=keys[i],i<l; i++) {
                    switch (k) {
                        case 'ctrl':
                        case 'control':
                            kp++;
                            modifiers.ctrl.wanted = true;
                            break;
                        case 'shift':
                        case 'alt':
                        case 'meta':
                            kp++;
                            modifiers[k].wanted = true;
                            break;
                    }

                    if (k.length > 1) { // If it is a special key
                        if(special_keys[k] == code) kp++;
                    } else if (opt['keyCode']) { // If a specific key is set into the config
                        if (opt['keyCode'] == code) kp++;
                    } else { // The special keys did not match
                        if(character == k) kp++;
                        else {
                            if(shift_nums[character] && e.shiftKey) { // Stupid Shift key bug created by using lowercase
                                character = shift_nums[character];
                                if(character == k) kp++;
                            }
                        }
                    }
                }

                if(kp == keys.length &&
                    modifiers.ctrl.pressed == modifiers.ctrl.wanted &&
                    modifiers.shift.pressed == modifiers.shift.wanted &&
                    modifiers.alt.pressed == modifiers.alt.wanted &&
                    modifiers.meta.pressed == modifiers.meta.wanted) {
            $timeout(function() {
                      callback(e);
            }, 1);

                    if(!opt['propagate']) { // Stop the event
                        // e.cancelBubble is supported by IE - this will kill the bubbling process.
                        e.cancelBubble = true;
                        e.preventDefault();

                        // e.stopPropagation works in Firefox.
                        if (e.stopPropagation) {
                            e.stopPropagation();
                            e.preventDefault();
                        }
                        return false;
                    }
                }

            };
            // Store shortcut
            keyboardManagerService.keyboardEvent[label] = {
                'callback': fct,
                'target':   elt,
                'event':    opt['type']
            };
            //Attach the function with the event
            if(elt.addEventListener) elt.addEventListener(opt['type'], fct, false);
            else if(elt.attachEvent) elt.attachEvent('on' + opt['type'], fct);
            else elt['on' + opt['type']] = fct;
        };
        // Remove the shortcut - just specify the shortcut and I will remove the binding
        keyboardManagerService.unbind = function (label) {
            label = label.toLowerCase();
            var binding = keyboardManagerService.keyboardEvent[label];
            delete(keyboardManagerService.keyboardEvent[label])
            if(!binding) return;
            var type        = binding['event'],
            elt         = binding['target'],
            callback    = binding['callback'];
            if(elt.detachEvent) elt.detachEvent('on' + type, callback);
            else if(elt.removeEventListener) elt.removeEventListener(type, callback, false);
            else elt['on'+type] = false;
        };
        //
        return keyboardManagerService;
    }]);

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

        Feeds.get = function (id) {
            if (isNaN(id)) {
                return Feeds[id];
            } else {
                return Feeds.list[id];
            }
        }

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

    feederate.factory('Entries', function (Restangular, Router, Feeds, $filter, $q) {
        var Entries = {
            entriesList: {},
            summariesList: {},
            allSummaries: [],
            daysList: [],
            activeEntry: null,
            activeSummary: null,
            noMore: false,
            areLoading: false,
            page: 0
        };

        var abort;

        Entries.reset = function () {
            Entries.inactivate();
            Entries.summariesList = {};
            Entries.allSummaries  = [];
            Entries.daysList      = [];
            Entries.noMore        = false;
            Entries.page          = 0;
            Feeds.active = null;
        };

        Entries.inactivate = function () {
            Entries.activeSummary = null;
            Entries.activeEntry   = null;
        };

        Entries.loadSummariesById = function (id, callback) {
            var request = Restangular.one(Router.get('get_summaries'), id).get()
                .then(function(summary) {
                    var feed = Feeds.list[summary.feed_id];
                    Entries.loadSummaries(feed, false, function () {
                        Entries.loadEntries(feed, function () {
                            Entries.setActive(summary);
                            callback();
                        });
                    })
                });
        };

        Entries.loadSummaries = function (feed, paginated, callback) {
            if (!feed) {
                return;
            }

            if (!paginated) {
                if (angular.equals(Feeds.active, feed)) {
                    return;
                }

                Entries.reset();
                Feeds.active = feed;
            }

            if (!paginated) {
                // Abort previous request if not ended          
                if (abort) {
                    abort.resolve();
                }

                Entries.areLoading = false;
            } else if (Entries.areLoading || Entries.noMore) {
                // If the load is in progress or if there are no more pages
                return;
            }      

            abort = $q.defer();

            var request = null,
                type    = Feeds.type(feed);

            Entries.areLoading = true;

            if (type === 'unread' || type === 'starred') {
                request = Restangular.all(Router.get('get_summaries')).withHttpConfig({timeout: abort.promise}).getList({
                    type: type,
                    page: Entries.page + 1
                });
            } else {
                request = Restangular.one(Router.get('get_feeds'), feed.id).withHttpConfig({timeout: abort.promise}).getList('summaries', {
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
                    Entries.allSummaries.push(summary);
                });

                Entries.page++;

                if (callback !== undefined) {
                    callback();
                }
            });
        };

        Entries.loadEntries = function (feed, callback) {
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

                if (callback !== undefined) {
                    callback();
                }
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
                    if ((Feeds.list[summary.feed_id].unread_count > 0) && (Feeds.unread.unread_count > 0)) {
                        Feeds.list[summary.feed_id].unread_count--;
                        Feeds.unread.unread_count--;
                    }
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
            if (!Entries.activeSummary) {
                return false;
            }

            return angular.equals(summary.id, Entries.activeSummary.id);
        };

        Entries.setActive = function(summary) {
            Entries.activeSummary = summary;

            var sanitizeResourceLink = function ($0, $1, $2) {
                if ($2.substring(0, 1) === '/') {
                    $2 = $2.substring(1);
                }

                return $1 + Feeds.list[summary.feed_id].target_url.replace(/\/$/, '') + '/' + $2;
            }

            // Replace relative img path
            Entries.entriesList[summary.id].content = Entries
                .entriesList[summary.id]
                .content
                .replace(/(<img[^>]+src=["'])((\/[^\/]|\.\/|\.\.\/)[^"']+)/gi, sanitizeResourceLink)
                .replace(/(<a[^>]+href=["'])((\/[^\/]|\.\/|\.\.\/)[^"']+)/gi, sanitizeResourceLink);

            Entries.activeEntry = Entries.entriesList[summary.id];
        }

        Entries.indexOf = function(summary) {
            for (var i in Entries.allSummaries) {
                if (Entries.allSummaries[i].id == summary.id) {
                    return i;
                }
            }

            return -1;
        }

        return Entries;
    });



    angular
        .module('feederate')
        .controller('BoardCtrl', function BoardCtrl ($scope, $window, Router, Feeds, Entries, Restangular, $location, $anchorScroll, keyboardManager) {

        angular.element(document).ready(function () {
            $scope.user           = null;
            $scope.Feeds          = Feeds;
            $scope.Entries        = Entries;
            $scope.selectedType   = 'summaries';
            $scope.selectedTypeId = null;
            $scope.oneColumn      = false;
            $scope.types          = ['feeds', 'summaries', 'entry'];


            var scrollOnMoving = function (container, elem) {
                var elemHeight      = elem.height();
                var elemTop         = elem.position().top;
                var containerHeight = container.height();

                if (containerHeight < elemHeight + elemTop) {
                    container.scrollTop(container.scrollTop() + elemHeight - containerHeight + elemTop);
                } else if (elemTop < 0) {
                    container.scrollTop(container.scrollTop() + elemTop);
                } 
            }

            keyboardManager.bind('left', function () {
                var index = $scope.types.indexOf($scope.selectedType);
                if (index > 0) {
                    if ($scope.selectedType == 'summaries') {
                        Entries.inactivate();
                    }

                    $scope.selectedType = $scope.types[$scope.types.indexOf($scope.selectedType) - 1];
                }
                $('.' + $scope.selectedType + '-content').focus();
                console.log($scope.selectedType);
            });

            keyboardManager.bind('right', function () {
                var index = $scope.types.indexOf($scope.selectedType);
                if (index < $scope.types.length - 1) {
                    if ($scope.selectedType == 'feeds') {
                        if (Entries.areLoading) {
                            return;
                        }

                        if (!Entries.activeSummary && Entries.allSummaries.length > 0) {
                            Entries.setActive(Entries.allSummaries[0]);
                        }
                    }

                    $scope.selectedType = $scope.types[$scope.types.indexOf($scope.selectedType) + 1];
                }
                $('.' + $scope.selectedType + '-content').focus();
                console.log($scope.selectedType);
            });

            keyboardManager.bind('down', function () {
                if ($scope.selectedType == 'feeds') {
                    var active = $('#feed_' + Feeds.active.id).next();
                    if (active.length) {
                        var id = active.attr('id').split('_');
                        $scope.loadSummaries(Feeds.get(id[1]));
                        scrollOnMoving($('.feeds-content'), $('#feed_' + Feeds.active.id));
                    }
                } else if ($scope.selectedType == 'summaries') {
                    if (Entries.activeSummary) {
                        var index = Entries.indexOf(Entries.activeSummary);
                        if ((index !== -1) && (Entries.allSummaries.length > ++index)) {
                            Entries.setActive(Entries.allSummaries[index]);
                        }
                        scrollOnMoving($('.summaries-content'), $('#summary_' + Entries.activeSummary.id));
                    }
                } else if ($scope.selectedType == 'entry') {
                    var container = $('.entry-content');
                    var elem      = container.children('.container');
                    if (container.height() < elem.height()) {
                        container.scrollTop(container.scrollTop() + container.height() * 0.1);
                    }
                }
            }, {propagate: false});

            keyboardManager.bind('up', function () {
                if ($scope.selectedType == 'feeds') {
                    var active = $('#feed_' + Feeds.active.id).prev();
                    if (active.length) {
                        var id = active.attr('id').split('_');
                        $scope.loadSummaries(Feeds.get(id[1]));
                        scrollOnMoving($('.feeds-content'), $('#feed_' + Feeds.active.id));
                    }
                } else if ($scope.selectedType == 'summaries') {
                    if (Entries.activeSummary) {
                        var index = Entries.indexOf(Entries.activeSummary);
                        if ((index !== -1) && (0 < index--)) {
                            Entries.setActive(Entries.allSummaries[index]);
                        }
                        scrollOnMoving($('.summaries-content'), $('#summary_' + Entries.activeSummary.id));
                    }
                } else if ($scope.selectedType == 'entry') {
                    var container = $('.entry-content');
                    var elem      = container.children('.container');
                    if (container.height() < elem.height()) {
                        container.scrollTop(container.scrollTop() - container.height() * 0.1);
                    }
                }
            }, {propagate: false});

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

            $scope.loadSummaries = function (feed, paginated, callback) {
                if (paginated === undefined) {
                    paginated = false;
                }

                Entries.loadSummaries(feed, paginated, function () {
                    $scope.loadEntries(feed, callback);
                });
            };

            $scope.loadSummariesById = function (id, callback) {
                Entries.loadSummariesById(id, callback);
            };

            $scope.loadEntries = function (feed, callback) {
                Entries.loadEntries(feed, callback);
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
            };

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
            };

            $scope.refreshFeedsAndEntries = function () {
                $scope.loadFeeds(function () {
                    var activeFeed = Feeds.active;
                    Entries.reset();
                    Feeds.active = activeFeed;
                    $scope.loadSummaries(Feeds.active);
                });
            }

            $scope.$watch(function () {
                return $location.path();
            }, function (path) {
                var splittedPath = path.replace(/^\/+|\/+$/g,'').split('/');

                if (splittedPath[1] == undefined) {
                    $scope.selectedType = 'feeds';
                } else {
                    if (splittedPath[0] == 'feeds') {
                        $scope.selectedType = 'summaries';
                        $scope.selectedTypeId = splittedPath[1];
                    } else {
                        $scope.selectedType = 'entry';
                        $scope.selectedTypeId = splittedPath[1];
                    }
                }
            });

            window.onresize = function () {
                setColumnMode();
            };

            var setColumnMode = function () {
                if ($(window).width() >= 768) {
                    $scope.oneColumn = false;
                } else {
                    $scope.oneColumn = true;
                }
            }

            setColumnMode();

            Restangular
                .one(Router.get('get_user'))
                .get()
                .then(function(user) {
                    $scope.user = user;
                    $scope.loadFeeds(function () {
                        if ($scope.selectedTypeId && $scope.selectedType == 'entry') {
                            $scope.loadSummariesById($scope.selectedTypeId, function () {
                                // Auto scroll into active feed and summary
                                $location.hash('feed_' + Feeds.active.id);
                                $anchorScroll();
                                $location.hash('summary_' + Entries.activeSummary.id);
                                $anchorScroll();
                            });
                        } else if ($scope.selectedTypeId && $scope.selectedType == 'summaries') {
                            $scope.loadSummaries(Feeds.get($scope.selectedTypeId), function () {
                                // Auto scroll into active feed
                                $location.hash('feed_' + Feeds.active.id);
                                $anchorScroll();
                            });
                        } else if (Feeds.unread.unread_count > 0) {
                            $scope.loadSummaries(Feeds.unread);
                        } else {
                            $location.path('feeds');
                        }
                    });
                });
        });
    });
})();
