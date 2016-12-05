angular.module('handcrowd', [
    'app.config',
    
    'ionic', 
    'ionic-datepicker',
    'ionic-timepicker',
    'ionic.rating',

    // Angular modules
    'ngRoute',

    // 3rd Party Modules
    'angularFileUpload',
    'angularMoment',
    'ngWebsocket',
    'duScroll',
    'ngCordova',
    'ngCordova.plugins.barcodeScanner',

    // common
    'app.api',
    'app.cache',
    'app.auth',
    'app.logger',
    'app.dateutil',
    'app.filters',
    'app.service.chat',
    'app.directives',
    'app.consts',

    // storage
    'app.storage',
    'app.storage.user',
    'app.storage.chat',
    'app.storage.home',
    'app.storage.mission',
    'app.storage.task',
    'app.storage.qr',

    // controller
    'app.signin',
    'app.signup',

    // home
    'app.home.menu',

    // bot
    'app.bot',

    // chatroom
    'app.chat.list',
    'app.chatroom',
    'app.chat.star',
    'app.chatroom.emoticon_add',

    // mission
    'app.mission.edit',
    'app.mission.summary',
    'app.mission.attach',
    'app.mission.member',

    // task
    'app.task.edit',
    'app.task.list',

    // proces
    'app.process',

    // member
    'app.member.list',

    // alert
    'app.alert.list',

    'app.tabs', 
    'app.settings'
    ]
)

.run(function($rootScope, $ionicPlatform, CONFIG, AUTH_EVENTS, $auth, logger,
        $syncServer, $location, $chat, $cordovaPush, $session, $api, userStorage, chatStorage, missionStorage, $state) {

    $rootScope.ver = "?v=" + CONFIG.VER
    $rootScope.error_disconnected = false

    $rootScope.$on('$stateChangeStart', function(event, next, current) {
        if (next.name != undefined) {
            if (next.name == "signin") 
                return;
        }
            
        authorizedRoles = null
        if (next.data)
            authorizedRoles = next.data.authorizedRoles;

        if (!authorizedRoles)
            return;

        if (!$auth.isAuthorized(authorizedRoles)) 
            $auth.autoLogin(null, authorizedRoles, event, function() {
                userStorage.register_push();
            });
    });

    $rootScope.$on('$stateChangeSuccess', function(event, current, previous, rejection) {
        console.log('stateChangeSuccess ' + $location.path());
    });

    $rootScope.$on('$stateChangeError', function(event, toState, toParams, fromState, fromParams, error) {
        console.log('stateChangeError ' + JSON.stringify(error));
    });

    $rootScope.$on(AUTH_EVENTS.loginSuccess, function() {
        $syncServer.sync(false);
    });

    $rootScope.$on('reload_session', function() {
        $syncServer.sync(false);
    });

    document.addEventListener("deviceready", function(){
        // cordova inappbrowser function
        window.open = cordova.InAppBrowser.open;
        
        $rootScope.cam_pictureSource = navigator.camera.PictureSourceType;
        $rootScope.cam_destinationType = navigator.camera.DestinationType;

        document.addEventListener('pause', function() {
            console.log("pause device");

            $rootScope.$apply();
            chatStorage.save_cache_messages_to_storage();
        }, false);

        document.addEventListener('resume', function() {
            console.log("resume phone");
            $chat.connect();

            $syncServer.sync(false);
            $rootScope.$apply();
            $rootScope.$broadcast('synced-server');

            chatStorage.save_cache_messages_to_storage();
        }, false);

        states = $session.statesFromStorage();

        window.handleOpenURL = function(url) {            
            if (url != '') {
                parts = url.split('?');
                if (parts && parts.length > 1) {
                    cmd = parts[0];
                    switch (cmd) {
                        case "handcrowd://signup":
                            params = parts[1].split('&');
                            if (params && params.length > 1) {
                                for (i = 0; i < params.length; i ++) {
                                    av = params[i].split('=');
                                    if (av) {
                                        if (av[0] == 'user_id')
                                            user_id = av[1];
                                        if (av[0] == 'activate_key')
                                            activate_key = av[1];
                                    }
                                }

                                if (user_id != undefined && activate_key != undefined) {                        
                                    console.log("User clicked activate mail: user_id=" + user_id + "activate_key=" + activate_key);
                                    $api.show_waiting();
                                    params = {
                                        user_id: user_id,
                                        activate_key: activate_key
                                    }

                                    $auth.activate(params).then(function(res) {
                                        $api.hide_waiting();
                                        if (res.err_code == 0) {
                                            logger.logSuccess("ユーザー登録が完了しました。ご利用いただきまして、ありがとうございます。");
                                            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                                            $state.transitionTo("tab.chats");
                                        }
                                        else {
                                            logger.logError(res.err_msg);
                                        }
                                    });
                                }
                            }
                            break;
                        case "handcrowd://invite_home":
                            params = parts[1].split('&');
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
                            break;
                        case "handcrowd://invite_chat":
                            params = parts[1].split('&');
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
                            break;
                    }
                }
            }
        }

        if ($rootScope.device_token == null) {        
            var os = $api.mobile_operating_system();
            var config = {};

            if(os == "iOS")
            {
                config = {
                    "badge": true,
                    "sound": true,
                    "alert": true,
                };
            }
            else if(os == "Android")
            {
                config = {
                    "senderID": "1000538079013",
                };
            }
            else
                return;

            $cordovaPush.register(config).then(function(device_token) {
                if(os == "iOS")
                {
                    $rootScope.device_token = device_token;
                    $session.statesToStorage();
                    console.log("Push Device Token: " + device_token);
                    userStorage.register_push();
                }
            }, function(err) {
                $rootScope.device_token = null;
                $session.statesToStorage();
                console.log("Device Token registration error: " + err);
            });
        }
        else {
            console.log("Already registered token:" + $rootScope.device_token);
            userStorage.register_push();
        }

    });

    $rootScope.$on('$cordovaPush:notificationReceived', function(event, notification) {
        var isIOS = ionic.Platform.isIOS();
        var isAndroid = ionic.Platform.isAndroid();                    
        if(isIOS)
        {
            if (notification.alert) {
                navigator.notification.alert(notification.alert);
            }

            if (notification.sound) {
                var snd = new Media(event.sound);
                snd.play();
            }

            $syncServer.sync(false);
            $rootScope.$apply();
            
            if (notification.badge) {

                $cordovaPush.setBadgeNumber(notification.badge).then(function(result) {
                    // Success!
                }, function(err) {
                    // An error occurred. Show a message to the user
                });
            }
        }
        else if(isAndroid)
        {
            console.log(notification.event);
            switch(notification.event) {
                case 'registered':
                    if (notification.regid.length > 0 ) {
                        $rootScope.device_token = notification.regid;
                        $session.statesToStorage();
                        console.log("Push Device Token: " + notification.regid);
                        userStorage.register_push();
                    }
                break;

                case 'message':
                    deviceNotification.add({
                        id: 30,
                        ticker: 'ticker title',
                        title: 'Handrowd メッセージ',
                        message: notification.message
                    });
                    // this is the actual push notification. its format depends on the data model from the push server
                    console.log('message = ' + notification.message);
                break;

                case 'error':
                    console.log('GCM error = ' + notification.msg);
                break;

                default:
                    console.log('An unknown GCM event has occurred');
                break;
            }            
        }
    });

    $ionicPlatform.ready(function() {
        if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
            cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
            cordova.plugins.Keyboard.disableScroll(true);
        }
        if (window.StatusBar) {
            // org.apache.cordova.statusbar required
            StatusBar.styleLightContent();
        }
    });
})

.config(
    function($stateProvider, $urlRouterProvider, USER_ROLES, CONFIG, $ionicConfigProvider) {
        $ionicConfigProvider.tabs.position('bottom');

        ver = "?v=" + CONFIG.VER
        $stateProvider

            .state('tab', {
                url: '/tab',
                abstract: true,
                templateUrl: 'templates/tabs.html' + ver
            })

            .state('signin', {
                url: '/signin',
                templateUrl: 'templates/account/signin.html' + ver,
                controller: 'signinCtrl'
            })

            .state('signup', {
                url: '/signup',
                templateUrl: 'templates/account/signup.html' + ver,
                controller: 'signupCtrl'
            })

            .state('signout', {
                url: '/signout',
                templateUrl: 'templates/account/signout.html' + ver,
                controller: 'signoutCtrl'
            })

            .state('tab.tasks', {
                url: '/tasks',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-tasks': {
                        templateUrl: 'templates/task/task_list.html' + ver,
                        controller: 'taskListCtrl'
                    }
                }
            })
            .state('tab.tasks.edit', {
                url: '/edit/:task_id',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-tasks@tab': {
                        templateUrl: 'templates/task/task_edit.html' + ver,
                        controller: 'taskEditCtrl'
                    }
                }
            })

            .state('tab.chats', {
                url: '/chats',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats': {
                        templateUrl: 'templates/tab-chats.html' + ver,
                        controller: 'chatsCtrl'
                    }
                }
            })

            .state('tab.chatroom', {
                url: '/chats/:mission_id/:chat_id?',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/chatroom/chatroom.html' + ver,
                        controller: 'chatroomCtrl'
                    }
                }
            })

            .state('tab.bot', {
                url: '/bot',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/home/bot.html' + ver,
                        controller: 'botCtrl'
                    }
                }
            })

            // edit chatroom
            .state('tab.chatroom.edit', {
                url: '/edit',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/mission/mission_edit.html' + ver,
                        controller: 'missionEditCtrl'
                    }
                }
            })

            // summary of chatroom
            .state('tab.chatroom.summary', {
                url: '/summary',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/mission/mission_summary.html' + ver,
                        controller: 'missionSummaryCtrl'
                    }
                }
            })

            // task of chatroom
            .state('tab.chatroom.task', {
                url: '/task',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/task/task_list.html' + ver,
                        controller: 'taskListCtrl'
                    }
                }
            })
            .state('tab.chatroom.task.edit', {
                url: '/edit/:task_id',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/task/task_edit.html' + ver,
                        controller: 'taskEditCtrl'
                    }
                }
            })

            // process of chatroom
            .state('tab.chatroom.process', {
                url: '/process',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/process/process.html' + ver,
                        controller: 'processCtrl'
                    }
                }
            })

            // attach of chatroom
            .state('tab.chatroom.attach', {
                url: '/attach',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/mission/mission_attach.html' + ver,
                        controller: 'missionAttachCtrl'
                    }
                }
            })

            // member of chatroom
            .state('tab.chatroom.member', {
                url: '/member',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/mission/mission_member.html' + ver,
                        controller: 'missionMemberCtrl'
                    }
                }
            })
            .state('tab.chatroom.member.add', {
                url: '/add',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/mission/mission_member_add.html' + ver,
                        controller: 'missionMemberAddCtrl'
                    }
                }
            })

            // add emoticion
            .state('tab.chatroom.emoticon_add', {
                url: '/emoticon_add',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/chatroom/emoticon_add.html' + ver,
                        controller: 'emoticonAddCtrl'
                    }
                }
            })

            // star
            .state('tab.chatroom.star', {
                url: '/star',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-chats@tab': {
                        templateUrl: 'templates/chatroom/chat_star.html' + ver,
                        controller: 'chatStarCtrl'
                    }
                }
            })

            .state('tab.member', {
                url: '/member',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-member': {
                        templateUrl: 'templates/member/member_list.html' + ver,
                        controller: 'memberListCtrl'
                    }
                }
            })

            .state('tab.alerts', {
                url: '/alerts',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-alerts': {
                        templateUrl: 'templates/tab-alerts.html' + ver,
                        controller: 'alertListCtrl'
                    }
                }
            })

            .state('tab.settings', {
                url: '/settings',
                data: { authorizedRoles: [USER_ROLES.user] },
                views: {
                    'tab-settings': {
                        templateUrl: 'templates/tab-settings.html' + ver,
                        controller: 'SettingsCtrl'
                    }
                }
            })

            .state("otherwise", { 
                url : '/404',
                templateUrl: 'templates/pages/404.html' + ver
            });

        // if none of the above states are matched, use this as the fallback
        $urlRouterProvider.otherwise('/tab/chats');
    }
);
