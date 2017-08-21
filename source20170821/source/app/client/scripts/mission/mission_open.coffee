'use strict'

angular.module('app.mission.open', [])

.controller('missionOpenCtrl', 
    ($scope, $api, $modalInstance, missionStorage, filterFilter, $rootScope, logger, $session, $dialogs, $timeout, private_flag) ->
        # Close dialog
        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        # Initialize
        $scope.init = () ->
            missionStorage.unpinned_missions($rootScope.cur_home.home_id, private_flag, (res) ->
                $scope.missions = res.missions
            )

        $scope.open = (mission) ->
            if private_flag == 2
                missionStorage.open_member($rootScope.cur_home.home_id, mission.user_id, (res) ->
                    if res.err_code == 0
                        $rootScope.$broadcast('refresh-missions')
                        logger.logSuccess("チャットルームを開きました。")
                        $scope.cancel()
                    else
                        logger.logError(res.err_msg)
                )
            else
                missionStorage.open(mission.mission_id, (res) ->
                    if res.err_code == 0
                        $rootScope.$broadcast('refresh-missions')
                        logger.logSuccess("チャットルームを開きました。")
                        $scope.cancel()
                    else
                        logger.logError(res.err_msg)
                )
            return

        $scope.init()

        return
)