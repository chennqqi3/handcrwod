angular.module('app.mission.member_add', [])

.controller('missionMemberAddCtrl', 
    ($scope, $rootScope, $modalInstance, $api, mission, search_string, $dialogs, logger, missionStorage, $chat, ALERT_TYPE) ->
        $scope.posting = false
        $scope.mission = mission
        $scope.search_string = search_string
        $scope.users = []
        $scope.selecteds = 0

        $scope.search = () ->
            missionStorage.invitable_members(mission.mission_id, (res) ->
                if res.err_code == 0
                    $scope.users = res.users
                else
                    logger.logError(res.err_msg)
                return
            )
            return

        $scope.selectUser = (user) ->
            if user.selected 
                user.selected = false
                $scope.selecteds = $scope.selecteds - 1
            else
                user.selected = true
                $scope.selecteds = $scope.selecteds + 1

        $scope.canInvite = ->
            return $scope.selecteds > 0

        $scope.invite = () ->
            if $scope.canInvite() 
                $scope.posting = true
                $scope.count = 0
                for user in $scope.users
                    if user.selected
                        $scope.count += 1
                        $scope.req =
                            user_id: user.user_id
                            mission_id: mission.mission_id
                            signup_url: $api.base_url() + "#/signup"
                            signin_url: $api.base_url() + "#/signin" 
                        missionStorage.invite($scope.req, (res) ->
                            $scope.count -= 1
                            $scope.posting = false
                            if res.err_code == 0
                                if res.user_id != null
                                    $chat.alert(ALERT_TYPE.INVITE_HOME, res.user_id, 
                                        home_id: $rootScope.cur_home.home_id
                                        home_name: $rootScope.cur_home.home_name
                                        mission_id: mission.mission_id
                                        mission_name: mission.mission_name
                                    )                        
                                logger.logSuccess(res.user_name + 'をチャットルームに招待しました。')
                                $rootScope.$broadcast('invite-user')
                                $scope.cancel()
                            else
                                logger.logError(res.err_msg)

                            if $scope.count == 0
                                $rootScope.$broadcast('refresh-missions')
                            return
                        )
                $modalInstance.close('select')
            else if !$api.is_empty($scope.search_string)
                $scope.invite_mission()

            return

        $scope.invite_mission = () ->
            $dialogs.inviteMission($scope.mission, $scope.search_string)
            $modalInstance.dismiss('cancel')
            return

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        $scope.search()
)