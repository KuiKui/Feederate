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
        Summaries: $resource(C.RESOURCE_URL + '/feeds/:feedId/summaries', {feedId:'@id'})
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

    $scope.loadFeeds();
}]);
