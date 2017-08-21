'use strict'

angular.module('app.consts', [])

.constant("HPRIV", 
    GUEST: 0         # ゲスト
    MEMBER: 1        # メンバー
    RMANAGER: 2      # ルーム管理者
    HMANAGER: 3      # グループ管理者
)
.constant("RPRIV", 
    MEMBER: 0        # メンバー
    MANAGER: 1       # ルーム管理者
)

.constant("ALERT_TYPE", 
    INVITE_HOME: 0         # グループ招待
)

.service('$consts', 
    ($rootScope, $timeout, $session, HPRIV, RPRIV) ->
        consts = this
        # Intialize global data
        consts.init = ->
            # week days
            $rootScope.g_week_days = [
                (id: 0, text: "日曜日")
                (id: 1, text: "月曜日")
                (id: 2, text: "火曜日")
                (id: 3, text: "水曜日")
                (id: 4, text: "木曜日")
                (id: 5, text: "金曜日")
                (id: 6, text: "土曜日")
            ]

            # repeat types
            $rootScope.g_repeat_types = [
                (id: 0, text: "なし")
                (id: 1, text: "毎日")
                (id: 2, text: "平日")
                (id: 3, text: "毎週")
                (id: 4, text: "毎月")
                (id: 5, text: "毎年")
            ]

            $rootScope.HPRIV = HPRIV
            $rootScope.get_priv_name = (priv) ->
                if priv == HPRIV.GUEST
                    return "ゲスト"
                else if priv == HPRIV.MEMBER
                    return "メンバー"
                else if priv == HPRIV.RMANAGER
                    return "ルーム管理者"
                else if priv == HPRIV.HMANAGER
                    return "グループ管理者"
                return ""

            $rootScope.RPRIV = RPRIV
            $rootScope.get_rpriv_name = (priv) ->
                if priv == RPRIV.MEMBER
                    return "メンバー"
                else if priv == RPRIV.MANAGER
                    return "管理者"
                return ""

            $rootScope.canEditTask = () ->
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv == HPRIV.HMANAGER || $rootScope.cur_home.priv == HPRIV.RMANAGER || $rootScope.cur_mission && $rootScope.cur_mission.priv == RPRIV.MANAGER)

            $rootScope.canChat = () ->
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv != HPRIV.GUEST || $rootScope.cur_mission != null && ($rootScope.cur_mission.private_flag == 2 || $rootScope.cur_mission.private_flag == 3))

            $rootScope.canOpenPrivChat = () ->
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv != HPRIV.GUEST)

            $rootScope.canEditMission = () ->
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv == HPRIV.HMANAGER || $rootScope.cur_home.priv == HPRIV.RMANAGER || $rootScope.cur_mission && $rootScope.cur_mission.priv == RPRIV.MANAGER)

            $rootScope.canEditMissionMember = () ->
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv == HPRIV.HMANAGER || $rootScope.cur_home.priv == HPRIV.RMANAGER || $rootScope.cur_mission && $rootScope.cur_mission.priv == RPRIV.MANAGER)

            $rootScope.canInviteChat = () ->
                return $rootScope.cur_home && $rootScope.cur_mission && ($rootScope.cur_mission.private_fla==0 || $rootScope.cur_mission.private_flag==1) && ($rootScope.cur_home.priv == HPRIV.HMANAGER || $rootScope.cur_home.priv == HPRIV.RMANAGER || $rootScope.cur_mission.priv == RPRIV.MANAGER)
                                
            $rootScope.canEditHome = ->
                return $rootScope.cur_home != null && $rootScope.cur_home.priv == HPRIV.HMANAGER

            $rootScope.canBreakHome = ->
                return true

            $rootScope.canEditHomeMember = ->
                return $rootScope.cur_home != null && $rootScope.cur_home.priv == HPRIV.HMANAGER

        return consts
)

.filter('priv_label',
    ($rootScope, HPRIV) ->
        return (input) ->
            return $rootScope.get_priv_name(input)
)

