'use strict';
angular.module('app.storage', [])

.service('$syncServer', 
    function($rootScope, $timeout, AUTH_EVENTS, homeStorage, missionStorage, taskStorage, chatStorage, $session, $consts) {
        var init, syncServer;
        syncServer = this;
        init = function() {
            $consts.init();

            // Task related data
            if ($rootScope.tasks === void 0) {
                $rootScope.tasks = [];
                $rootScope.priorityTasks = 0;
                $rootScope.remainingITasks = 0;
                $rootScope.remainingMTasks = 0;
            }
            if ($rootScope.complete_offsets === void 0) {
                $rootScope.complete_offsets = {};
            }
            if ($rootScope.calendar_date === void 0) {
                $rootScope.calendar_date = null;
            }
            $rootScope.remainingSelTasks = 0;

            // Home related data
            if ($rootScope.homes == undefined) {
                $rootScope.homes = [];
            }
            if ($rootScope.cur_home === void 0) {
                $rootScope.cur_home = null;
            }
            $rootScope.cur_home_name = function() {
                if ($rootScope.cur_home !== null) {
                    return $rootScope.cur_home.home_name;
                } else {
                    return "「グループなし」";
                }
            };

            // Mission related data
            if ($rootScope.missions === void 0) {
                $rootScope.missions = [];
            }
            $rootScope.mission_complete_offset = 0;
            if ($rootScope.cur_mission === void 0) {
                $rootScope.cur_mission = null;
            }

            if ($rootScope.groups === void 0) {
                $rootScope.groups = [];
                $rootScope.members = [];
            }
            if ($rootScope.templates === void 0) {
                $rootScope.templates = [];
            }
            if ($rootScope.device_token == undefined) {
                $rootScope.device_token = null;
            }
            if ($rootScope.alerts === void 0) {
                $rootScope.alerts = [];
            }
        };
        init();
        $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() {
            $rootScope.tasks = [];
            $rootScope.priorityTasks = 0;
            $rootScope.remainingITasks = 0;
            $rootScope.remainingMTasks = 0;
            $rootScope.complete_offsets = {};
            $rootScope.calendar_date = null;
            $rootScope.remainingSelTasks = 0;
            $rootScope.missions = [];
            $rootScope.cur_mission = null;
            $rootScope.mission_complete_offset = 0;
            $rootScope.groups = [];
            $rootScope.members = [];
            $rootScope.templates = [];
            $rootScope.alerts = [];
        });
        syncServer.sync = function(resync) {
            if ($session.user_id !== null && $session.user_id !== void 0) {
                return homeStorage.search().then(function() {
                    if ($rootScope.cur_home !== null) {
                        missionStorage.search($rootScope.cur_home.home_id).then(function() {
                            chatStorage.refresh_unreads_title();
                            return $rootScope.$broadcast("synced-server");

                            /*
                            $timeout( ->
                                    $('.sync-server').find('i').removeClass('glyphicon-spin')
                                    if resync == true
                                            $timeout( ->
                                                    syncServer.sync(true)
                                            , 120000)
                            )
                             */
                        });
                        homeStorage.bot_messages($rootScope.cur_home.home_id, false);
                    }
                    return
                });
            }
        };
        $rootScope.$on('refresh-homes', function(event, msg) {
            return syncServer.sync();
        });

        $rootScope.$on('refresh-home-logo', function(event, home_id) {
            return homeStorage.refresh_logo(home_id);
        });

        $rootScope.$on('refresh-missions', function(event, msg) {
            if ($rootScope.cur_home !== null) {
                return missionStorage.search($rootScope.cur_home.home_id);
            }
        });
        $rootScope.$on('refresh-tasks', function(event, msg) {
            if ($rootScope.cur_mission !== null) {
                return taskStorage.search($rootScope.cur_mission.mission_id);
            }
        });
        $rootScope.$on('select-home', function(event, new_mission_id) {
            if ($rootScope.cur_home !== null) {
                missionStorage.search($rootScope.cur_home.home_id);
                return homeStorage.bot_messages($rootScope.cur_home.home_id, false);
            }
        });

        $rootScope.$on('removed_home', function() {
            homeStorage.search().then(function(homes) {
                if (homes.length > 0)
                    homeStorage.select(homes[0]);
                else
                    $rootScope.cur_home = null;
            });
        });
        return syncServer;
    }
)

.directive('syncServer', 
    function($timeout, $rootScope, $parse, $api, $session, $syncServer) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs, ngModel) {
                var init;
                init = function() {
                    return $(element).click(function() {
                        return $syncServer.sync();
                    });
                };
                return $timeout(function() {
                    return init();
                }, 10);
            }
        };
    }
)

.factory('templateStorage', 
    function($rootScope, $api, $session, $dateutil, filterFilter, AUTH_EVENTS, $auth) {
        var init, search;
        init = function() {
            if ($auth.isAuthenticated()) {
                return search();
            }
        };
        search = function() {
            return $api.call("template/search").then(function(res) {
                if (res.data.err_code === 0) {
                    $rootScope.templates = res.data.templates;
                    return $rootScope.templates;
                } else {
                    return [];
                }
            });
        };
        return {
            init: init,
            search: search
        };
    }
)

.factory('searchHistoryStorage', 
    function() {
        var EMPTY, STORAGE_ID;
        STORAGE_ID = 'search_history';
        EMPTY = '[]';
        return {
            get: function() {
                var err, error;
                try {
                    return JSON.parse(localStorage.getItem(STORAGE_ID) || EMPTY);
                } catch (error) {
                    err = error;
                    return [];
                }
            },
            put: function(history) {
                return localStorage.setItem(STORAGE_ID, JSON.stringify(history));
            }
        };
    }
);