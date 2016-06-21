
angular.module('app.bot', [])

.controller('botCtrl', 
    function($scope, $api, $chat, missionStorage, homeStorage, $rootScope, $location, $routeParams, logger, $session, $timeout, HPRIV) {
        $rootScope.nav_id = 'bot';

        if ($rootScope.bot_params == undefined) {
            $rootScope.bot_params = {
                self_only: false
            }
        }
        
        // Search
        $scope.customFilter = function(task) {
            if ($rootScope.bot_params.self_only)
                return task.performer_id == $session.user_id;
            else
                return true;
        }
        return;
    }   
);