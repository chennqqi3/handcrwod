angular.module('app.mission.add', [])

.controller('missionAddCtrl', 
    ($scope, $rootScope, $modalInstance, missionStorage, $api, logger, $location, $timeout) ->
        $scope.posting = false
        $scope.mission = 
            home_id: $rootScope.cur_home.home_id
            mission_name: ""
            private_flag: 0

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        # Check privilege
        $scope.canSubmit = ->
            return $scope.form_mission_add.$valid

        # Add mission
        $scope.ok = ->
            if (!$scope.posting)
                $scope.posting = true
                missionStorage.add($scope.mission, (res) ->
                    if res.err_code == 0
                        $rootScope.$broadcast('refresh-missions', res.mission_id)
                        $scope.cancel()
                        logger.logSuccess('新しいチャットルームが作成されました。')
                    else
                        logger.logError(res.err_msg)
                    $scope.posting = false
                )
)