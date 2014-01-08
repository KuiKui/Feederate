var app = angular.module('feederate', ['ngResource', 'ngSanitize']);

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
        Entries: $resource(C.RESOURCE_URL + '/feeds/:feedId/entries', {feedId:'@id'})
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
    $scope.activeFeed  = null;
    $scope.activeEntry = null;

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

    $scope.loadEntries = function (feed) {
        Rest.Entries.query({feedId: feed.id}, function (entries) {
            $scope.entries     = entries;
            $scope.activeFeed  = feed;
            $scope.activeEntry = null;
        });
    };

    $scope.isActiveEntry = function (entry) {
        return angular.equals(entry, $scope.activeEntry);
    };

    $scope.loadEntry = function (entry) {
        $scope.entry = entry;
        $scope.activeEntry  = entry;
    };

    $scope.loadFeeds();
}]);
