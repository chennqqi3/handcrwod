'use strict'

angular.module('app.mission.edit', [])

.controller('missionEditCtrl', 
    ($rootScope, $scope, $api, $modalInstance, filterFilter, missionStorage, 
        logger, $session, $dialogs, $timeout, mission, $location) ->
        # Close dialog
        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        # Initialize
        $scope.init = () ->
            $scope.editSummaryMode = false
            $scope.editMissionNameMode = false
            mission.org_mission_name = mission.mission_name
            mission.org_summary = mission.summary
            mission.org_private_flag = mission.private_flag
            mission.org_push_flag = mission.push_flag
            $scope.mission = mission

        $scope.init()

        # Check privilege
        $scope.canEdit = ->
            return $rootScope.canEditMission() && $scope.mission.private_flag != 3

        $scope.canRepeat = ->
            return $session.planconfig != null && $session.planconfig.repeat_flag == true

        $scope.canBackImage = ->
            return $session.planconfig != null && $session.planconfig.back_image_flag == true
            
        # Edit mission name
        $scope.changeMissionName = (mission) ->
            mission.mission_name = mission.mission_name.trim()
            if mission.mission_name == mission.org_mission_name
                $scope.editMissionNameMode = false
                return
            if mission.mission_name == ""
                mission.mission_name = mission.org_mission_name
            else
                params = 
                    mission_id: mission.mission_id
                    mission_name: mission.mission_name

                missionStorage.edit(params, (data) ->
                    if data.err_code != 0
                        logger.logError(data.err_msg)
                    else
                        mission.org_mission_name = mission.mission_name
                        $rootScope.cur_mission.mission_name = mission.mission_name
                        $rootScope.$broadcast('refresh-missions')
                )

            $scope.editMissionNameMode = false

        $scope.editMissionName = (mission) ->
            if $scope.canEdit()
                $scope.editMissionNameMode = true

        # Edit summary
        $scope.editSummary = () ->
            if $scope.canEdit()
                $scope.editSummaryMode = true
            return

        $scope.exitEditSummary = () ->
            $scope.mission.summary = $scope.mission.org_summary
            $scope.editSummaryMode = false
            return

        $scope.submitSaveSummary = (mission) ->
            params =
                mission_id: mission.mission_id
                summary: mission.summary
            missionStorage.edit(params)
            $scope.mission.org_summary = $scope.mission.summary
            $scope.editSummaryMode = false
            return

        # Member label
        $scope.memberLabel = (member)->
            if member.user_id == $scope.mission.client_id
                return "管理者"
            else
                return ""

        # Upload background image for process
        $scope.onUploadBackImage = (files, type) ->
            if files.length == 0
                return
            file = files[0]
            missionStorage.upload_back_image($scope.mission.mission_id, type, file).progress( (evt) ->
                #console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total))
            ).success( (data, status, headers, config) ->
                if data.err_code == 0
                    $scope.mission.job_back = data.job_back
                    $scope.mission.job_back_url = data.job_back_url
                    $scope.mission.job_back_pos = data.job_back_pos
                    $scope.mission.prc_back = data.prc_back
                    $scope.mission.prc_back_url = data.prc_back_url
                    $scope.mission.prc_back_pos = data.prc_back_pos
                    missionStorage.set_mission($scope.mission)
                    $rootScope.cur_mission = $scope.mission
                    logger.logSuccess("背景画像を変更しました。")

                    angular.element("input[type='file']").val('')

                    $rootScope.$broadcast('refresh_back_image')
                else
                    logger.logError(data.err_msg)
            )

        $scope.onSettingBackImage = (type) ->
            $dialogs.settingBackImage($scope.mission, type)

        # Set repeat info
        $scope.setRepeat = ->
            missionStorage.set_repeat($scope.mission.mission_id, $scope.mission.repeat_type,
                $scope.mission.repeat_weekday, $scope.mission.repeat_month, $scope.mission.repeat_monthday,
                (data) ->
                    if data.err_code != 0
                        logger.logError(data.err_msg)
            )
            return

        # change private flag
        $scope.changeMissionPrivateFlag = (mission) ->
            if parseInt(mission.org_private_flag, 10) == parseInt(mission.private_flag, 10)
                return

            if mission.private_flag == '0'
                message = "このチャットルームをパブリックルームに変更してもよろしいでしょうか？"
            else if mission.private_flag == '1'
                message = "このチャットルームをプライベートルームに変更してもよろしいでしょうか？"
            else
                return

            $dialogs.confirm('確認', message, 'OK', "confirm-change-private-flag", mission, "cancel-change-private-flag")
            return

        $scope.$on('confirm-change-private-flag', () ->
            mission.private_flag = parseInt(mission.private_flag, 10)
            params = 
                mission_id: mission.mission_id
                private_flag: mission.private_flag

            missionStorage.edit(params, (data) ->
                if data.err_code != 0
                    logger.logError(data.err_msg)
                else
                    mission.org_private_flag = mission.private_flag
                    $rootScope.cur_mission.private_flag = mission.private_flag
                    $rootScope.$broadcast('refresh-missions')
            )
        )

        $scope.$on('cancel-change-private-flag', () ->
            mission.private_flag = mission.org_private_flag
        )

        # change alert flag
        $scope.changeMissionAlertFlag = (mission) ->
            if parseInt(mission.org_push_flag, 10) == parseInt(mission.push_flag, 10)
                return

            mission.push_flag = parseInt(mission.push_flag, 10)
            params = 
                mission_id: mission.mission_id
                push_flag: mission.push_flag

            missionStorage.edit(params, (data) ->
                if data.err_code != 0
                    logger.logError(data.err_msg)
                else
                    mission.org_push_flag = mission.push_flag
                    $rootScope.cur_mission.push_flag = mission.push_flag
                    $rootScope.$broadcast('refresh-missions')
            )

            return


        return
)