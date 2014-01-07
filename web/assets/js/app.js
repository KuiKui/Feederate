var app = angular.module('feederate', ['ngResource']);

app.factory('Constants', [
    function() {
        return {
            RESOURCE_URL: "/app_dev.php/api"
        }
    }
]);

app.factory('Rest', ['Constants', '$resource', function(C, $resource) {
    return {
        Feeds: $resource(C.RESOURCE_URL + '/feeds')
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
    }

    $scope.loadFeeds();
}]);
