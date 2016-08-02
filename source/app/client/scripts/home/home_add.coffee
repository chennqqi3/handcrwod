angular.module('app.home.add', [])

.controller('homeAddCtrl', 
    ($scope, $rootScope, $modalInstance, homeStorage, $api, logger) ->
        $scope.posting = false
        $scope.home = 
            home_name: ""

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        # Check privilege
        $scope.canSubmit = ->
            return $scope.form_home_add.$valid

        # Add home
        $scope.ok = ->
            if (!$scope.posting)
                $scope.posting = true
                homeStorage.add($scope.home, (res) ->
                    if res.err_code == 0
                        $rootScope.$broadcast('added_home', res.home)
                        $scope.cancel()
                        logger.logSuccess('新しいグループが作成されました。')
                    else
                        logger.logError(res.err_msg)
                    $scope.posting = false
                )
)