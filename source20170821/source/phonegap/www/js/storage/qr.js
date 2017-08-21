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

                                homeStorage.invite_from_qr(home_id, invite_key, callback);
                            }
                            return;
                        }
                        // handcrowd://invite_home?id=:home_id&key=:invite_key
                        pattern = 'handcrowd://invite_home?';
                        i = url.indexOf(pattern);
                        if (i > 0) {
                            url = url.substring(i + pattern.length);
                            params = url.split('&');
                            if (params && params.length > 1) {
                                for (i = 0; i < params.length; i ++) {
                                    av = params[i].split('=');
                                    if (av) {
                                        if (av[0] == 'id')
                                            home_id = av[1];
                                        if (av[0] == 'key')
                                            invite_key = av[1];
                                    }
                                }

                                if (home_id != undefined && invite_key != undefined) {
                                    homeStorage.invite_from_qr(home_id, invite_key);
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

                                missionStorage.invite_from_qr(mission_id, invite_key, callback);
                            }
                            return;
                        }
                        // handcrowd://invite_chat?id=:mission_id&key=:invite_key
                        pattern = 'handcrowd://invite_chat?';
                        i = url.indexOf(pattern);
                        if (i > 0) {
                            url = url.substring(i + pattern.length);
                            params = url.split('&');
                            if (params && params.length > 1) {
                                for (i = 0; i < params.length; i ++) {
                                    av = params[i].split('=');
                                    if (av) {
                                        if (av[0] == 'id')
                                            mission_id = av[1];
                                        if (av[0] == 'key')
                                            invite_key = av[1];
                                    }
                                }

                                if (mission_id != undefined && invite_key != undefined) {
                                    missionStorage.invite_from_qr(mission_id, invite_key);
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