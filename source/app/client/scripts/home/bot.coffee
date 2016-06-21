'use strict'

angular.module('app.bot', [])

.controller('botCtrl', 
    ($scope, $api, $chat, missionStorage, homeStorage, $rootScope, $location, 
        $routeParams, logger, $session, $timeout, $dialogs, HPRIV) ->   
        $rootScope.nav_id = 'bot'

        if $rootScope.bot_params == undefined
            $rootScope.bot_params = 
                self_only: false
        
        # Search
        $scope.customFilter = (task) ->
            if $rootScope.bot_params.self_only
                return task.performer_id == $session.user_id
            else
                return true
        return
)