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
        readSummary: $resource(C.RESOURCE_URL + '/summaries/:id/read', {id:'@id'})
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
    $scope.entries = [];

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

    $scope.loadEntries = function (feedId) {
        Rest.Entries.query({feedId: feedId}, function (entries) {
            for (var index in entries) {
                $scope.entries[entries[index].id] = entries[index];
            }
        });
    };

    $scope.loadSummaries = function (feedId) {
        Rest.Summaries.query({feedId: feedId}, function (summaries) {
            $scope.summaries = summaries;
        });
    };

    $scope.loadEntry = function (summaries) {
        $scope.entry = $scope.entries[summaries.id];
    };

    $scope.markAsRead = function (summary) {
        Rest.readSummary.save({id: summary.id}, {is_read: true});
        summary.is_read = true;
    };

    $scope.loadFeeds();
}]);
