'use strict';
angular.module('app.consts', [])

.constant("HPRIV", {
    GUEST: 0,        // ゲスト
    MEMBER: 1,       // メンバー
    RMANAGER: 2,     // ルーム管理者
    HMANAGER: 3      // ホーム管理者
    }
)

.constant("ALERT_TYPE", {
    INVITE_HOME: 0         // ホーム招待
    }
)

.service('$consts', 
    function($rootScope, $timeout, $session, HPRIV) {
        var consts;
        consts = this;
        consts.init = function() {
            $rootScope.g_week_days = [
                { id: 0, text: "日曜日" }, 
                { id: 1, text: "月曜日" }, 
                { id: 2, text: "火曜日" }, 
                { id: 3, text: "水曜日" }, 
                { id: 4, text: "木曜日" }, 
                { id: 5, text: "金曜日" }, 
                { id: 6, text: "土曜日" }
            ];
            $rootScope.g_repeat_types = [
                { id: 0, text: "なし" }, 
                { id: 1, text: "毎日" }, 
                { id: 2, text: "平日" }, 
                { id: 3, text: "毎週" }, 
                { id: 4, text: "毎月" }, 
                { id: 5, text: "毎年" }
            ];

            $rootScope.HPRIV = HPRIV;
            $rootScope.get_priv_name = function(priv) {
                if (priv == HPRIV.GUEST)
                    return "ゲスト";
                else if (priv == HPRIV.MEMBER)
                    return "メンバー";
                else if (priv == HPRIV.RMANAGER)
                    return "ルーム管理者";
                else if (priv == HPRIV.HMANAGER)
                    return "ホーム管理者";
                return "";
            };

            $rootScope.canEditTask = function() {
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv == HPRIV.HMANAGER || $rootScope.cur_home.priv == HPRIV.RMANAGER);
            };
            $rootScope.canChat = function() {
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv != HPRIV.GUEST || $rootScope.cur_mission != null && $rootScope.cur_mission.private_flag == 2);
            };
            $rootScope.canOpenPrivChat = function() {
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv != HPRIV.GUEST);
            };
            $rootScope.canEditMission = function() {
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv == HPRIV.HMANAGER || $rootScope.cur_home.priv == HPRIV.RMANAGER);
            };
            $rootScope.canEditMissionMember = function() {
                return $rootScope.cur_home != null && ($rootScope.cur_home.priv == HPRIV.HMANAGER || $rootScope.cur_home.priv == HPRIV.RMANAGER);
            };
            $rootScope.canEditHome = function(home) {
                return home.priv == HPRIV.HMANAGER;
            };
            $rootScope.canEditHomeMember = function() {
                return $rootScope.cur_home != null && $rootScope.cur_home.priv == HPRIV.HMANAGER;
            };
        };
        return consts;
    }
);