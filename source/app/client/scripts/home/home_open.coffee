'use strict'

angular.module('app.home.open', [])

.controller('homeOpenCtrl', 
    ($scope, $api, $modalInstance, homeStorage, filterFilter, $rootScope, logger, $session, $dialogs, $timeout) ->
        # Close dialog
        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        # Initialize
        $scope.init = () ->
            homeStorage.search()

        $scope.open = (home) ->
            homeStorage.select(home)
            $scope.cancel()
            return

        $scope.init()

        return
)