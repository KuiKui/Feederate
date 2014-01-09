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

var app = angular.module('feederate', ['ngResource', 'ngSanitize', 'truncate']);

app.factory('Constants', [
    function() {
        return {
            RESOURCE_URL: "/app_dev.php/api"
        }
    }
]);

app.factory('Rest', ['Constants', '$resource', function(C, $resource) {
    return {
        Feeds: $resource(C.RESOURCE_URL + '/feeds'),
        Entries: $resource(C.RESOURCE_URL + '/feeds/:feedId/entries', {feedId:'@id'}),
        Summaries: $resource(C.RESOURCE_URL + '/feeds/:feedId/summaries', {feedId:'@id'}),
        readSummary: $resource(C.RESOURCE_URL + '/summaries/:id/read', {id:'@id'}),
        starSummary: $resource(C.RESOURCE_URL + '/summaries/:id/star', {id:'@id'})
    }
}]);

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

app.controller('BoardCtrl', ['$scope', 'Rest', function BoardCtrl ($scope, Rest) {
    $scope.activeFeed    = null;
    $scope.activeSummary = null;
    $scope.entries       = [];

    $scope.addFeed = function () {
        Rest.Feeds.save({
            title: $scope.newFeedUrl,
            url: $scope.newFeedUrl,
            targetUrl: $scope.newFeedUrl
        });

        $scope.newFeedUrl = '';

        $scope.loadFeeds();
    };

    $scope.loadFeeds = function () {
        Rest.Feeds.query(function (feeds) {
            $scope.feeds = feeds;
        });
    };

    $scope.isActiveFeed = function (feed) {
        return angular.equals(feed, $scope.activeFeed);
    };

    $scope.loadSummaries = function (feed) {
        Rest.Summaries.query({feedId: feed.id}, function (summaries) {
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
        Rest.Entries.query({feedId: feed.id}, function (entries) {
            for (var index in entries) {
                $scope.entries[entries[index].id] = entries[index];
            }
        });
    };

    $scope.loadEntry = function (summary) {
        $scope.entry         = $scope.entries[summary.id];
        $scope.activeSummary = summary;
    };

    $scope.markAsRead = function (summary) {
        if (!summary.is_read) {
            Rest.readSummary.save({id: summary.id}, {is_read: true});
            summary.is_read = true;
            $scope.activeFeed.unread_count--;
        }
    };

    $scope.markAsStarred = function (summary) {
        Rest.starSummary.save({id: summary.id}, {is_starred: !summary.is_starred});
        summary.is_starred = !summary.is_starred;
    };

    $scope.loadFeeds();
}]);
