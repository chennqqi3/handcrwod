'use strict';

angular.module('app.controller.nav', [])

.controller('NavCtrl', 
    ($scope, $rootScope, $session, AUTH_EVENTS, $timeout, $location, $dialogs, $route, missionStorage, logger, $api) ->
        app = $('#app')

        $scope.init = ()->
            $scope.session = $session
           
        $scope.init()

        $scope.$on(AUTH_EVENTS.loginSuccess, (event, count) ->
            $scope.init()
        )

        $scope.$on('reload_session', (event, count) ->
            $scope.init()
        )

        $scope.$on('synced-server', (event, count) ->
            $scope.init()
        )

        $scope.showAlerts = ->
            $dialogs.showAlerts()

        # Mission related
        $scope.addMission = () ->
            $dialogs.addMission()
            return

        $scope.openMission = (private_flag) ->
            $dialogs.openMission(private_flag)
            return

        $scope.pinMission = (mission) ->
            if mission.pinned == 1
                pinned = 0
            else
                pinned = 1
            missionStorage.pin(mission.mission_id, pinned, (res) ->
                if res.err_code == 0
                    $rootScope.$broadcast('refresh-missions')
                else
                    logger.logError(res.err_msg)
            )
            return

        # ホームへの招待
        $scope.inviteMember = ->
            $dialogs.inviteHome($rootScope.cur_home)

        # Search
        $scope.roomFilter = (mission) ->
            return mission.private_flag == 0 || mission.private_flag == 1
        $scope.memberFilter = (mission) ->
            return mission.private_flag == 2 && mission.user_id != $session.user_id
        $scope.botFilter = (mission) ->
            return mission.private_flag == 3
        $scope.visibleFilter = (mission) ->
            return mission.visible

        # toggle group
        $scope.groups = [true, true, true]
        $scope.toggleGroup = (index) ->
            $scope.groups[index] = !$scope.groups[index]    

        $scope.selectLogout = ->
            message = "ログアウトします。よろしいでしょうか？"
            $dialogs.confirm('ログアウト', message, 'ログアウト', "logout")
            return

        $scope.$on('logout', () ->
            app.removeClass('expanded')
            $location.path("/signout")
        )

        # search
        $scope.search_string = ''
        $scope.onSelectSearchMessage = (message) ->
            if !$api.is_empty($scope.cur_mission) && message.mission_id == $scope.cur_mission.mission_id
                $rootScope.$broadcast('scroll-to-message', message.cmsg_id)
            else
                $location.path("/chats/" + message.mission_id + "/" + message.cmsg_id)
            return

        $scope.search = () ->
            if !$api.is_empty($scope.search_string)
                $dialogs.chatSearch(false, $scope.search_string, $scope.onSelectSearchMessage)
                $scope.search_string = ''
            return

        $scope.exitSearch = () ->
            $scope.search_string = ''

        $scope.open_member = (mission) ->
            if mission.mission_id != null
                $location.path("/chats/" + mission.mission_id)
            else            
                missionStorage.open_member($rootScope.cur_home.home_id, mission.user_id, (res) ->
                    if res.err_code == 0
                        new_mission_id = res.mission_id
                        missionStorage.search($rootScope.cur_home.home_id, $rootScope.cur_home.include_completed).then(()->
                            $location.path("/chats/" + new_mission_id)
                        )
                    else
                        logger.logError(res.err_msg)
                )
)


.controller('NavHomeCtrl', 
    ($scope, $rootScope, $session, AUTH_EVENTS, $route, homeStorage, logger, $dialogs, $location) ->
        app = $('#app')

        $scope.open = (home) ->
            homeStorage.select(home, ->
                $location.path('/home')
            )
            return

        # ホーム追加  
        $scope.addHome = ->
            $dialogs.addHome()

        $scope.$on('added_home', (event, home) ->
            if $rootScope.cur_home == null
                homeStorage.select(home)
                $rootScope.$broadcast('refresh-missions')
            else
                $rootScope.$broadcast('refresh-homes')
                
            return
        )
)
