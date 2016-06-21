'use strict'

angular.module('app.mission.member', [])

.controller('missionMemberCtrl', 
    ($scope, $rootScope, $api, $modalInstance, missionStorage, filterFilter, logger, $session, $dialogs, $timeout, mission) ->
        # Close dialog
        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        # Initialize
        $scope.init = () ->
            $scope.editSummaryMode = false
            $scope.editMissionNameMode = false
            $scope.mission = mission

        $scope.init()

        # Check privilege
        $scope.canInvite = ->
            return $rootScope.canEditMissionMember()

        $scope.canRemoveUser = (member)->
            return $rootScope.canEditMissionMember()

        # Remove member
        $scope.removeUser = (user) ->
            message = "このユーザーを共有解除してもよろしいでしょうか？"
            $dialogs.confirm('共有解除', message, '確認', "deinvite-user", user)
            return

        $scope.$on('deinvite-user', (event, user) ->
            missionStorage.remove_member($scope.mission.mission_id, user.user_id, (res) ->
                if res.err_code == 0
                    logger.logSuccess(user.user_name + "が共有解除されました。")
                    for i in [0..$scope.mission.members.length - 1]
                        if $scope.mission.members[i].user_id == user.user_id
                            $scope.mission.members.splice(i, 1)
                            break
                    for i in [0..$rootScope.cur_mission.members.length - 1]
                        if $rootScope.cur_mission.members[i].user_id == user.user_id
                            $rootScope.cur_mission.members.splice(i, 1)
                            break
                    $scope.init()
                else
                    logger.logError(res.err_msg)
                return
            )
            return
        )

        # Member label
        $scope.memberLabel = (member)->
            if member.user_id == $scope.mission.client_id
                return "管理者"
            else
                return ""

        # Invite User
        $scope.addMissionMember = (mission, search_string)->
            $dialogs.addMissionMember(mission, search_string)
            $scope.cancel()
            return

        # show user profile
        $scope.showUserProfile = (user_id) ->
            $dialogs.showUserProfile(user_id)
            return

        return
)