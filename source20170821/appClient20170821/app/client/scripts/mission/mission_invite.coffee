angular.module('app.mission.invite', [])

.controller('missionInviteCtrl', 
    ($scope, $rootScope, $modalInstance, $api, mission, email, 
        logger, $timeout, $session, missionStorage, $chat, ALERT_TYPE) ->
        content = $session.user_name + "( " + $session.email + " )様より、「ハンドクラウド」へ招待されました。\n" + 
            "招待されたチャットルームは、下記の通りです。\n" +
            "チャットルーム名:" + mission.mission_name + "\n"

        $scope.posting = false
        if mission.summary != null
            content = content + "概要:\n" + mission.summary

        $scope.req =
            email: email
            content: content
            mission_id: mission.mission_id
            signup_url: $api.base_url() + "#/signup"
            signin_url: $api.base_url() + "#/signin"

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        $scope.ok = ->
            $scope.posting = true
            missionStorage.invite($scope.req, (res) ->
                $scope.posting = false
                if res.err_code == 0
                    if res.user_id != null
                        $chat.alert(ALERT_TYPE.INVITE_HOME, res.user_id, 
                            home_id: $rootScope.cur_home.home_id
                            home_name: $rootScope.cur_home.home_name
                            mission_id: mission.mission_id
                            mission_name: mission.mission_name
                        )
                    logger.logSuccess('チャットルームに招待しました。')
                    $scope.cancel()
                else
                    logger.logError(res.err_msg)
            )
        
        $scope.canSubmit = ->
            return $scope.form_mission_invite.$valid && !$scope.posting

)