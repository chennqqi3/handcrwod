'use strict'

angular.module('app.home', [])

.controller('homeCtrl', 
    ($scope, $api, $chat, missionStorage, homeStorage, $rootScope, $location, 
        $routeParams, logger, $session, $timeout, $dialogs, HPRIV) ->        
        $rootScope.nav_id = 'home'
        missionStorage.select_mission_in_nav()
        
        $scope.sync = ->
            if ($rootScope.cur_home != null)
                if $rootScope.cur_home.include_completed == undefined
                    $rootScope.cur_home.include_completed = false

                homeStorage.get($rootScope.cur_home.home_id, 
                    $rootScope.cur_home.include_completed, 
                    $rootScope.cur_home.include_completed, 
                    (res) ->
                        if res.err_code == 0
                            $scope.home = res.home
                )

        $scope.$on("synced-server", ->
            $scope.sync()
        ) 

        # グループ編集
        $scope.editHome = ->
            if $scope.home != null
                $dialogs.editHome($scope.home)
          
        # グループ追加  
        $scope.addHome = ->
            $dialogs.addHome()

        $scope.$on('added_home', (event, home) ->
            if $rootScope.cur_home == null
                homeStorage.select(home)
                $rootScope.$broadcast('refresh-missions')
                $scope.sync()
            else
                $rootScope.$broadcast('refresh-homes')
                
            return
        )

        $scope.$on('select-home', (event, home) ->
            $scope.sync()
            return
        )

        # Mission related
        $scope.addMission = () ->
            $dialogs.addMission()
            return

        # Refresh list of missions
        $scope.$on('refresh-missions', (event, new_mission_id) ->
            $scope.sync()
        )

        # Remove mission
        $scope.removeMission = (mission) ->
            message = mission.mission_name + "を削除してもよろしいでしょうか？"
            $dialogs.confirm('チャットルーム削除', message, '削除', "remove-mission", mission)
            return

        $scope.$on('remove-mission', (event, mission) ->
            missionStorage.remove(mission, (res) ->
                if res.err_code == 0
                    $rootScope.$broadcast('refresh-missions')
                    logger.logSuccess('チャットルームが削除されました。')
                else
                    logger.logError(res.err_msg)
            )
            return
        )

        # 管理者権限設定
        $scope.selPriv = (member) ->
            $dialogs.selPriv(member.priv, (priv) ->
                homeStorage.priv($scope.home.home_id, member.user_id, priv, (res) ->
                    if res.err_code == 0
                        member.priv = res.priv
                        member.priv_name = $rootScope.get_priv_name(res.priv)

                        if member.user_id == $session.user_id
                            $rootScope.cur_home.priv = res.priv
                            $session.setCurHome($rootScope.cur_home)
                    else
                        logger.logError(res.err_msg)
                )
            )

        # メンバー削除
        $scope.removeMember = (member) ->
            $dialogs.confirm('メンバー削除', '「' + member.user_name + '」をグループから削除します。よろしいでしょうか？', '確認', ->
                homeStorage.remove_member($scope.home.home_id, member.user_id, (res) ->
                    if res.err_code == 0
                        $rootScope.$broadcast('refresh-missions')
                        logger.logSuccess("メンバーをグループから削除しました。")
                    else
                        logger.logError(res.err_msg)
                )
                return
            )

        # グループへの招待
        $scope.inviteMember = ->
            $dialogs.inviteHome($scope.home)

        # 検索
        $scope.onSelectSearchMessage = (message) ->
            $location.path("/chats/" + message.mission_id + "/" + message.cmsg_id)
            return

        $scope.search = () ->
            if !$api.is_empty($scope.search_string)
                $dialogs.chatSearch(false, $scope.search_string, $scope.onSelectSearchMessage)
            return

        $scope.toggleCompleted = (private_flag) ->
            if $rootScope.cur_home != null
                $rootScope.cur_home.include_completed = !$rootScope.cur_home.include_completed

                missionStorage.search($rootScope.cur_home.home_id, $rootScope.cur_home.include_completed)

        # Import csv
        $scope.importCSV = ()->
            $dialogs.importCSV($rootScope.cur_home.home_id)
            return

        $scope.$on('import-csv', (event, file) ->
            $api.import_csv($rootScope.cur_home.home_id, file).progress( (evt) ->
                #console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total))
            ).success( (data, status, headers, config) ->
                if data.err_code == 0
                    logger.logSuccess(data.imported + "件のタスクが登録されました。")

                    $rootScope.$broadcast('refresh-missions')
                    $rootScope.$broadcast('refresh-tasks')
                else
                    logger.logError(data.err_msg)

                angular.element("input[type='file']").val('')
            )
            return
        )

        # グループの削除
        $scope.removeHome = ->
            $dialogs.confirm('グループ削除', 'このグループを削除してもよろしいでしょうか？', '確認', ->
                $dialogs.confirm('グループ削除', 'グループを削除すると元に戻すことができなくなります。よろしいでしょうか？', 'OK', ->
                    homeStorage.remove($scope.home.home_id, (res) ->
                        if res.err_code == 0
                            logger.logSuccess("グループを削除しました。")
                            $rootScope.$broadcast('removed_home')
                        else
                            logger.logError(res.err_msg)
                    )
                , null, 'btn-danger')    
            )

        # グループの退会
        $scope.breakHome = ->
            $dialogs.confirm('グループ退会', 'このグループから退会します。よろしいでしょうか？', '確認', ->
                $dialogs.confirm('グループ削除', 'グループから退会すると元に戻すことができなくなります。よろしいでしょうか？', 'OK', ->
                    homeStorage.break_home($scope.home.home_id, (res) ->
                        if res.err_code == 0
                            logger.logSuccess("グループを退会しました。")
                            $rootScope.$broadcast('removed_home')
                        else
                            logger.logError(res.err_msg)
                    )
                    return
                , null, 'btn-danger')    
            )

        # Search
        $scope.publicFilter = (mission) ->
            return mission.private_flag == 0
        $scope.privateFilter = (mission) ->
            return mission.private_flag == 1
        $scope.memberFilter = (mission) ->
            return mission.private_flag == 2

        $scope.open_member = (mission) ->
            if mission.mission_id != null
                $location.path("/chats/" + mission.mission_id)
            else
                if mission.user_id != $session.user_id
                    missionStorage.open_member($rootScope.cur_home.home_id, mission.user_id, (res) ->
                        if res.err_code == 0
                            new_mission_id = res.mission_id
                            missionStorage.search($rootScope.cur_home.home_id, $rootScope.cur_home.include_completed).then(()->
                                $location.path("/chats/" + new_mission_id)
                            )
                        else
                            logger.logError(res.err_msg)
                    )

        $scope.sync()
        return
)