angular.module('app.storage.qr', [])
.factory('qrStorage', 
    function($rootScope, $api, $session, $cordovaBarcodeScanner, logger, homeStorage, missionStorage, $state, $ionicPopup) {
        scan = function(callback) {
            $cordovaBarcodeScanner.scan()
                .then(function(result) {
                    if (result.format == 'QR_CODE' && result.cancelled == false && result.text != '')
                    {
                        var mission_id, home_id;
                        url = result.text;

                        // /#/home/:home_id
                        pattern = '/#/home/';
                        i = url.indexOf(pattern);
                        if (i > 0) {
                            home_id = url.substring(i + pattern.length);
                            home = homeStorage.get_home(home_id);

                            if (home != null) {
                                homeStorage.select(home);
                            }
                            else {
                                logger.logError("グループが存在しないとかアクセス権限がありません。");
                            }
                            return;
                        }
                        // /#/qr/home/:home_id/:invite_key
                        pattern = '/#/qr/home/';
                        i = url.indexOf(pattern);
                        if (i > 0) {
                            url = url.substring(i + pattern.length);
                            params = url.split('/');
                            if (params.length == 2) {
                                home_id = params[0];
                                invite_key = params[1];

                                home = homeStorage.get_home(home_id);
                                if (home != null)
                                    homeStorage.select(home);
                                else {
                                    homeStorage.get_name(home_id, function(res) {
                                        if (res.err_code == 0 && !$api.is_empty(res.home_name)) {
                                            $ionicPopup.confirm({
                                                title: 'グループ招待',
                                                template: 'グループ「' + res.home_name + '」に招待されます。よろしいでしょうか？',
                                                buttons: [
                                                    { text: 'キャンセル' },
                                                    {
                                                        text: '<b>OK</b>',
                                                        type: 'button-positive',
                                                        onTap: function(e) {
                                                            homeStorage.self_invite(home_id, invite_key, function(res) {
                                                                if (res.err_code == 0) {
                                                                    homeStorage.search().then(function() {
                                                                        home = homeStorage.get_home(home_id);
                                                                        if (home != null)
                                                                            homeStorage.select(home);
                                                                    });
                                                                }
                                                                else
                                                                    logger.logError(res.err_msg);
                                                            });
                                                        }
                                                    }
                                                ]
                                            });
                                        }
                                    });
                                }
                            }
                            return;
                        }

                        // /#/chats/:mission_id
                        pattern = '/#/chats/';
                        i = url.indexOf(pattern);
                        if (i > 0) {
                            mission_id = url.substring(i + pattern.length);
                            $state.go('tab.chatroom', {mission_id: mission_id});
                            return;
                        }
                        // /#/qr/chat/:mission_id/:invite_key
                        pattern = '/#/qr/chat/';
                        i = url.indexOf(pattern);
                        if (i > 0) {
                            url = url.substring(i + pattern.length);
                            params = url.split('/');
                            if (params.length == 2) {
                                mission_id = params[0];
                                invite_key = params[1];

                                mission = missionStorage.get_mission(mission_id);
                                if (mission != null)
                                    missionStorage.select(mission);
                                else {
                                    missionStorage.get_name(mission_id, function(res) {
                                        if (res.err_code == 0 && !$api.is_empty(res.mission_name)) {
                                            $ionicPopup.confirm({
                                                title: 'グループ招待',
                                                template: 'チャットルーム「' + res.mission_name + '」に招待されます。よろしいでしょうか？',
                                                buttons: [
                                                    { text: 'キャンセル' },
                                                    {
                                                        text: '<b>OK</b>',
                                                        type: 'button-positive',
                                                        onTap: function(e) {
                                                            missionStorage.self_invite(mission_id, invite_key, function(res) {
                                                                if (res.err_code == 0) {
                                                                    homeStorage.search().then(function() {
                                                                        $state.go('tab.chatroom', {mission_id: mission_id});
                                                                    });
                                                                }
                                                                else
                                                                    logger.logError(res.err_msg);
                                                            });
                                                        }
                                                    }
                                                ]
                                            });
                                        }
                                    });
                                }
                            }
                            return;
                        }
                    }
                }, function(error) {
                    // An error occurred
                });
        };

        return {
            scan: scan
        };
    }
);