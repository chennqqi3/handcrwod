'use strict';

angular.module('app.qr', [])

.controller('qrHomeCtrl', 
    ($scope, $location, $session, $rootScope, $api, $routeParams, homeStorage, $dialogs, $window, logger, $timeout) ->
        home_id = parseInt($routeParams.home_id, 10)
        invite_key = $routeParams.invite_key

        if $window.isIOS() || $window.isAndroid()
            $timeout(->
                document.location.href = "handcrowd://invite_home?id=" + home_id + "&key=" + invite_key
            , 1000)
        else
            if $session.user_id == null
                # is not logined yet
                $rootScope.redirect_url = '/qr/home/' + home_id + '/' + invite_key
                $location.path('/signin')
            else
                home = homeStorage.get_home(home_id)
                if home != null
                    $location.path('/home/' + home_id)
                else
                    homeStorage.get_name(home_id, (res) ->
                        if res.err_code == 0 && !$api.is_empty(res.home_name)
                            $dialogs.confirm('招待', 'グループ「' + res.home_name + '」に参加します。よろしいでしょうか？', '確認', ->
                                homeStorage.self_invite(home_id, invite_key, (res) ->
                                    if res.err_code == 0
                                        homeStorage.search().then(->
                                           $location.path('/home/' + res.home_id)
                                        )
                                    else
                                        logger.logError(res.err_msg)
                                )
                            )
                        else
                            logger.logError("グループが存在しません。")
                            $location.path('/home')
                    )
        return
)
.controller('qrChatCtrl', 
    ($scope, $location, $session, $rootScope, $api, $routeParams, missionStorage, homeStorage, $dialogs, $window, logger, $timeout) ->
        mission_id = parseInt($routeParams.mission_id, 10)
        invite_key = $routeParams.invite_key

        if $window.isIOS() || $window.isAndroid()
            $timeout(->
                document.location.href = "handcrowd://invite_chat?id=" + mission_id + "&key=" + invite_key
            , 1000)
        else
            if $session.user_id == null
                # is not logined yet
                $rootScope.redirect_url = '/qr/chat/' + mission_id + '/' + invite_key
                $location.path('/signin')
            else
                mission = missionStorage.get_mission(mission_id)
                if mission != null
                    $location.path('/chats/' + mission_id)
                else
                    missionStorage.get_name(mission_id, (res) ->
                        if res.err_code == 0 && !$api.is_empty(res.mission_name)
                            $dialogs.confirm('招待', 'チャットルーム「' + res.mission_name + '」に参加します。よろしいでしょうか？', '確認', ->
                                missionStorage.self_invite(mission_id, invite_key, (res) ->
                                    if res.err_code == 0
                                        homeStorage.search().then(->
                                           $location.path('/chats/' + res.mission_id)
                                        )
                                    else
                                        logger.logError(res.err_msg)
                                )
                            )        
                        else    
                            logger.logError("チャットルームが存在しません。")
                            $location.path('/home')
                    )
        return
)