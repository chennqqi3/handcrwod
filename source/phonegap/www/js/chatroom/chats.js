angular.module('app.chat.list', [])

.controller('chatsCtrl', 
    function($scope, $rootScope, $api, $session, $ionicPopup, $ionicPopover, $ionicModal, $ionicListDelegate, $ionicScrollDelegate, $ionicActionSheet, homeStorage, missionStorage, chatStorage, logger, $timeout, $state, qrStorage) {
    viewScroll = $ionicScrollDelegate.$getByHandle('missionScroll');    

    var keyboardHeight = 0;

    // toggle group
    $scope.groups = [true, true, true];
    $scope.toggleGroup = function(index) {
        $scope.groups[index] = !$scope.groups[index];
        show = $scope.groups[index];

        for (i = 0; i < $rootScope.missions.length; i ++) {
            mission = $rootScope.missions[i];

            if (index == 0) {
                if (mission.private_flag == 0 || mission.private_flag == 1)
                {
                    if (show)
                        $('#mission_' + mission.mission_id).removeClass('hide');
                    else {
                        if (!mission.visible)
                            $('#mission_' + mission.mission_id).addClass('hide');
                    }
                }
            }
            else if (index == 2) {
                if (mission.private_flag == 2 && mission.user_id != $session.user_id)
                {
                    if (show)
                        $('#mission2_' + mission.user_id).removeClass('hide');
                    else {
                        if (!mission.visible)
                            $('#mission2_' + mission.user_id).addClass('hide');
                    }
                }
            }
        }
    }

    // search chat message
    $scope.prev_id = null;
    $scope.next_id = null;
    $scope.messages = [];
    $scope.search = {text: ""};
    var MAX_MSG_LENGTH = 100;

    $ionicModal.fromTemplateUrl('templates/chatroom/chat_search.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function(modal) {
        $scope.modalChatSearch = modal;
    });
    /* search bar */
    $scope.show_search_bar = false;
    $scope.open_search_bar = function() {
        $scope.show_search_bar = true;
        $rootScope.hide_navbar = true;
        $('.tabs-icon-top').addClass('tabs-hide');
    }
    $scope.close_search_bar = function() {
        $scope.show_search_bar = false;
        $rootScope.hide_navbar = false;
        $('.tabs-icon-top').removeClass('tabs-hide');

        $scope.search.text = '';
        $scope.search_chats();
    }
    $scope.search_chats = function() {
        query = $scope.search.text;
        if (query != '')
            query = query.toUpperCase();
        viewScroll.scrollTop(true);
        if ($rootScope.cur_home) {
            len = $rootScope.missions.length;
            for (n = 0; n < len; n ++) {
                mission = $rootScope.missions[n];

                if (!(mission.private_flag == 0 || mission.private_flag == 1))
                    continue;

                id = missionStorage.mission_html_id(mission);

                if (query == '' || mission.mission_name.toUpperCase().indexOf(query) != -1)
                    $('#' + id).show();
                else
                    $('#' + id).hide();
            }

            len = $rootScope.missions.length;
            for (n = 0; n < len; n ++) {
                mission = $rootScope.missions[n];

                if (!(mission.private_flag == 2 && mission.user_id != $session.user_id && mission.accepted == 1))
                    continue;

                id = missionStorage.mission_html_id(mission);

                if (query == '' || mission.mission_name.toUpperCase().indexOf(query) != -1)
                    $('#' + id).show();
                else
                    $('#' + id).hide();
            }

            if (query != '') {
                $('#room_divider').hide();
                $('#member_divider').hide();
            }
            else {
                $('#room_divider').show();
                $('#member_divider').show();
            }
        }
    }
    $scope.open_search_chat = function() {
        $scope.show_search_chat = false;
        $rootScope.hide_navbar = false;

        $scope.init_search_result();
        $scope.modalChatSearch.show();

        if ($scope.search.text != '')
            $scope.search_chat();
    }
    $scope.init_search_result = function() {
        $scope.prev_id = null;
        $scope.next_id = null;
        $scope.messages = [];
    }
    $scope.search_chat = function(init) {
        if($scope.search.text == "")
            return;

        if(init)
            $scope.init_search_result();

        var search_string = $scope.search.text;
        $api.show_waiting();
        chatStorage.search_messages($rootScope.cur_home.home_id, null, search_string, $scope.prev_id, $scope.next_id).then(function(messages) {
            $api.hide_waiting();
            if (messages.length > 0) {
                if ($scope.next_id !== null) {
                    for (var i = 0; i < messages.length; i++)
                        $scope.messages.push(messages[i]);
                    /*
                    if($scope.messages.length > MAX_MSG_LENGTH)
                        $scope.messages.splice(0, $scope.messages.length - MAX_MSG_LENGTH);
                    */
                    $scope.startScrollTimer($scope.next_id, "next");
                } else if ($scope.prev_id !== null) {
                    for (var i = 0; i<messages.length; i++)
                        $scope.messages.splice(i, 0, messages[i]);
                    /*
                    if($scope.messages.length > MAX_MSG_LENGTH)
                        $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length-MAX_MSG_LENGTH);
                    */
                    $scope.startScrollTimer($scope.prev_id, "prev");                        
                } else {
                    $scope.messages = messages;
                }
            }
        });    
    }
    $scope.onMessageScrollComplete = function() {
        var viewScroll = $ionicScrollDelegate.$getByHandle('userMessageScroll');
        var elem = angular.element(document.querySelector('#chat_view'));
        var scrollTop = viewScroll.getScrollPosition().top;
        var scrollHeight = elem[0].scrollHeight;
        var viewHeight = elem[0].offsetHeight;

        console.log("scrollHeight:" + scrollHeight
                     + " scrollTop:" + scrollTop 
                     + " viewHeight:" + viewHeight);

        if(scrollTop <= 1)
            $scope.prev();
        else if(scrollTop + viewHeight <= scrollHeight+1 && scrollTop + viewHeight >= scrollHeight-1)
            $scope.next();        
    }
    $scope.scrollTimer = null;
    $scope.startScrollTimer = function(cmsg_id, type) {
        if ($scope.scrollTimer !== null) {
            $scope.stopScrollTimer();
        }
        return $scope.scrollTimer = $timeout(function() {
            var elem = angular.element(document.querySelector('#chat_' + cmsg_id));
            var view = angular.element(document.querySelector('#chat_view'));
            var viewHeight = view[0].offsetHeight;

            if(elem && elem[0])
            {
                var rect = elem[0].getBoundingClientRect();                
                var scrollTop = 0;
                if(type == "prev")
                {
                    var elemTop = rect.top;
                    var firstElemId = $scope.messages[0].cmsg_id;
                    var firstElem = angular.element(document.querySelector('#chat_' + firstElemId));
                    var firstElemTop = firstElem[0].getBoundingClientRect().top;

                    scrollTop = elemTop - firstElemTop + 2;
                }
                else if(type == "next")
                {
                    var elemTop = rect.top;
                    var firstElemId = $scope.messages[0].cmsg_id;
                    var firstElem = angular.element(document.querySelector('#chat_' + firstElemId));
                    var firstElemTop = firstElem[0].getBoundingClientRect().top;

                    scrollTop = elemTop - firstElemTop - view[0].offsetHeight + rect.height + 20;
                }

                var viewScroll = $ionicScrollDelegate.$getByHandle('userMessageScroll');
                viewScroll.scrollTo(rect.left, scrollTop);
            }
            $scope.stopScrollTimer();
        });

        console.log("startScrollTimer() " + cmsg_id);            
    };
    $scope.stopScrollTimer = function() {
        if ($scope.scrollTimer !== null) {
            $timeout.cancel($scope.scrollTimer);
            return $scope.scrollTimer = null;
        }
    };
    $scope.close = function() {
        $scope.modalChatSearch.hide();
        $scope.init_search_result();
        $scope.close_search_bar();
    };

    // menu
    $ionicPopover.fromTemplateUrl('chats-menu.html', {
        scope: $scope
    }).then(function(popover) {
        $scope.otherMenu = popover;
    });

    $scope.showOthers = function($event) {
        $scope.otherMenu.show($event);
    }

    $scope.hideOthers = function($event) {
        $scope.otherMenu.hide($event);   
    }

    $scope.$on('$destroy', function() {
        $scope.otherMenu.remove();
        $scope.modalChatSearch.remove();
        $scope.init_search_result();
        $scope.close_search_bar();
    });
    $scope.$on('$stateChangeStart', function(event, next, current) {
        $scope.close_search_bar();
    });
    $scope.$on('modal.hidden', function() {
        // Execute action
    });
    $scope.$on('modal.removed', function() {
        // Execute action
    });
    $scope.prev = function() {
        if ($scope.messages.length > 0) {
            $scope.prev_id = $scope.messages[0].cmsg_id;
            $scope.next_id = null;
            $scope.search_chat(false);
        }
    };
    $scope.next = function() {
        var length;
        length = $scope.messages.length;
        if ($scope.messages.length > 0) {
            $scope.prev_id = null;
            $scope.next_id = $scope.messages[length - 1].cmsg_id;
            $scope.search_chat(false);
        }
    };
    $scope.onMessageHold = function(e, itemIndex, message) {
        console.log('onMessageHold');
        console.log('message: ' + JSON.stringify(message, null, 2));

        var buttons = [{
                text: '<i class="ion-share icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">メッセージへ移動</i>'
            }];

        $ionicActionSheet.show({
            buttons: buttons,
            buttonClicked: function(index) {
                switch (index) {
                    case 0: // go
                        $scope.goMessage(message);
                        break;
                }

                return true;
            }
        });
    };    
    $scope.goMessage = function(message) {
        $state.transitionTo("tab.chatroom", {mission_id: message.mission_id, chat_id: message.cmsg_id});
        $scope.modalChatSearch.hide();
    };

    // Mission related
    $scope.addMission = function(private_flag) {
        $scope.mission = {
            home_id: $rootScope.cur_home.home_id,
            mission_name: '',
            private_flag: private_flag
        }

        // An elaborate, custom popup
        var popNewMission = $ionicPopup.show({
            template: '<ion-list>' +
                '<label class="item item-input"><input type="text" ng-model="mission.mission_name" required placeholder="ルーム名を入力してください。"></label>' + 
                '</ion-list>',
            title: 'チャットルームの新規作成',
            scope: $scope,
            buttons: [
                { text: 'キャンセル' },
                {
                    text: '<b>OK</b>',
                    type: 'button-positive',
                    onTap: function(e) {
                        if (!$scope.mission.mission_name) {
                            e.preventDefault();
                        } else {
                            return $scope.mission;
                        }
                    }
                }
            ]
        });

        popNewMission.then(function(mission) {
            if (mission != undefined) {
                var popNewMissionPrivateFlag = $ionicPopup.show({
                    template: '<ion-list><ion-radio ng-value="1" ng-model="mission.private_flag">特定メンバー用</ion-radio>' + 
                        '<ion-radio ng-value="0" ng-model="mission.private_flag">全メンバー用</ion-radio>' +
                        '</ion-list>',
                    title: 'チャットルームのタイプ',
                    scope: $scope,
                    buttons: [
                        { text: 'キャンセル' },
                        {
                            text: '<b>OK</b>',
                            type: 'button-positive',
                            onTap: function(e) {
                                if (!$scope.mission.mission_name) {
                                    e.preventDefault();
                                } else {
                                    return $scope.mission;
                                }
                            }
                        }
                    ]
                });

                popNewMissionPrivateFlag.then(function(mission) {
                    if (mission != undefined) {
                        missionStorage.add(mission, function(res) {
                            if (res.err_code == 0) {
                                $rootScope.$broadcast('refresh-missions', res.mission_id);
                                logger.logSuccess('新しいチャットルームが作成されました。');
                                $state.transitionTo("tab.chatroom", {mission_id: res.mission_id});
                            }
                            else
                                logger.logError(res.err_msg);
                        });
                    }
                });
            }
        });
        return;
    }

    $scope.openMission = function(private_flag) {
        $dialogs.openMission(private_flag);
        return;
    }

    $scope.pinMission = function(mission_id) {
        if (mission_id == null)
            return;
        for (var i = 0; i < $rootScope.missions.length; i ++) {
            if ($rootScope.missions[i].mission_id == mission_id) {
                mission = $rootScope.missions[i];
                if (mission.pinned == 1)
                    pinned = 0;
                else
                    pinned = 1;

                missionStorage.pin(mission_id, pinned, function(res) {
                    if (res.err_code == 0) {
                        mission.pinned = pinned;
                        id = "#" + missionStorage.mission_html_id(mission);
                        if (mission.pinned) {
                            $(id + ' .pin').removeClass('hide');
                            $(id + ' .btn-pin').addClass('hide');
                            $(id + ' .btn-unpin').removeClass('hide');
                        }
                        else {
                            $(id + ' .pin').addClass('hide');
                            $(id + ' .btn-pin').removeClass('hide');
                            $(id + ' .btn-unpin').addClass('hide');
                        }
                    }
                    else
                        logger.logError(res.err_msg);

                });
                $ionicListDelegate.closeOptionButtons();
                break;
            }
        } 
        return;
    }

    $scope.breakMission = function(mission_id) {
        if (mission_id == null)
            return;
        for (var i = 0; i < $rootScope.missions.length; i ++) {
            if ($rootScope.missions[i].mission_id == mission_id) {
                mission = $rootScope.missions[i];

                var confirmPopup = $ionicPopup.confirm({
                    title: 'チャットルームから退室',
                    template: '「' + mission.mission_name + '」から退室します。よろしいでしょうか？',
                    buttons: [
                        { text: 'キャンセル' },
                        {
                            text: '<b>確認</b>',
                            type: 'button-positive',
                            onTap: function(e) {
                                $timeout(function() {
                                    var confirmPopup2 = $ionicPopup.confirm({
                                        title: 'チャットルームから退室',
                                        template: 'チャットルームから退室すると元に戻すことができなくなります。よろしいでしょうか？',
                                        buttons: [
                                            { text: 'キャンセル' },
                                            {
                                                text: '<b>OK</b>',
                                                type: 'button-positive',
                                                onTap: function(e) {
                                                    missionStorage.break_mission(mission_id, function(res) {
                                                        if (res.err_code == 0) {
                                                            logger.logSuccess('チャットルームから退室しました。');
                                                            $rootScope.$broadcast('refresh-missions');
                                                        }
                                                        else
                                                            logger.logError(res.err_msg);
                                                    });
                                                }
                                            }
                                        ]
                                    });
                                    confirmPopup2.then();
                                });
                            }
                        }
                    ]
                });
                confirmPopup.then();

                $ionicListDelegate.closeOptionButtons();
                break;
            }
        } 
        return;
    }

    // Refresh list of missions
    $scope.init = function(scroll_top) {
        if (scroll_top == undefined)
            scroll_top = false;
        $scope.loaded = false;
        if ($rootScope.cur_home != null) {
            $api.show_waiting();
            missionStorage.search($rootScope.cur_home.home_id)
                .then(function(missions) {
                    $scope.loaded = true;
                    $api.hide_waiting();
                    var viewScroll = $ionicScrollDelegate.$getByHandle('missionScroll');
                    if (scroll_top)
                        viewScroll.scrollTop(true);
                });

            homeStorage.emoticons($rootScope.cur_home.home_id);
        }
    }

    $scope.$on('refresh-missions', function(event, new_mission_id) {
        $scope.init();
    });

    $scope.$on('refresh-homes', function(event, new_mission_id) {
        $scope.init();
    });

    $scope.$on('select-home', function(event, new_mission_id) {
        $scope.init(true);
    });

    $scope.$on('synced-server', function(event) {
        $scope.init();
    });

    $scope.$on('$ionicView.beforeEnter', function(event) {
        $scope.init();
    });

    $scope.$on('unread-message', function(event, mission) {
        id = "#" + missionStorage.mission_html_id(mission);
        if (mission.last_text!='' && mission.last_text!=null)
            $(id).addClass('with-last-text');
        $(id + ' .unreads').html(missionStorage.mission_unreads_to_html(mission));

        if (mission.private_flag == 0 || mission.private_flag == 1) {
            $(id).insertAfter("#room_divider");
        }
        else {
            $(id).insertAfter("#member_divider");   
        }
        
    });

    $scope.open_member = function(user_id) {
        for (var i = 0; i < $rootScope.missions.length; i ++) {
            mission = $rootScope.missions[i];
            if (mission.private_flag == 2 && mission.user_id == user_id) {
                if (mission.mission_id != null)
                    $state.transitionTo("tab.chatroom", {mission_id: mission.mission_id});
                else {
                    missionStorage.open_member($rootScope.cur_home.home_id, mission.user_id, function(res) {
                        if (res.err_code == 0) {
                            new_mission_id = res.mission_id;
                            missionStorage.search($rootScope.cur_home.home_id)
                                .then(function() {
                                    $state.transitionTo("tab.chatroom", {mission_id: new_mission_id});
                                });
                        }
                        else
                            logger.logError(res.err_msg);
                    });
                }
            }
        }
    };

    $scope.scan_qr = function() {
        $scope.hideOthers();
        qrStorage.scan();
    };

    $scope.show_unread_hint = false;
    get_top_of_hidden_unreads = function() {
        var avartar, el, id, logout, mission, offset, parentRect, _i, _len, _ref;
        var elem = angular.element(document.querySelector('#chats_view'));
        var scrollTop = viewScroll.getScrollPosition().top;
        console.log("scroll " + scrollTop);
        var scrollHeight = elem[0].scrollHeight;
        var viewHeight = elem[0].offsetHeight;

        if ($rootScope.missions) {
            _ref = $rootScope.missions;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                mission = _ref[_i];
                if (mission.unreads > 0) {
                    id = missionStorage.mission_html_id(mission);
                    el = angular.element(document.querySelector('#' + id))[0];
                    if (el) {
                        offset = el.getBoundingClientRect();
                        console.log("offset" + offset.top);
                        if (offset && offset.top > viewHeight) {
                            return scrollTop + offset.top + offset.height;
                        }
                    }
                }
            }
        }
        return null;
    };
    $scope.check_hidden_unreads = function() {
        if ((get_top_of_hidden_unreads() !== null)) {
            $('.unread_hint').show();
        }
        else {
            $('.unread_hint').hide();
        }
    };

    $scope.scroll_unread = function() {
        var top = get_top_of_hidden_unreads();
        var elem = angular.element(document.querySelector('#chats_view'));
        h = elem[0].offsetHeight;
        top = top > h ? (top - h ) : h;
        viewScroll.scrollTo(0, top);
        return;
    };

    $scope.$on('rendered-missions', function(event) {
        $scope.check_hidden_unreads();
    });

    function resizeLayout() {
        $('#chats_search_bar').css('bottom', keyboardHeight + "px");
    }

    // keyboard processiong
    ionic.on('native.keyboardshow', onShowKeyboard, window);
    ionic.on('native.keyboardhide', onHideKeyboard, window);

    function onShowKeyboard(e) {
        if (ionic.Platform.isAndroid() && !ionic.Platform.isFullScreen) {
            return;
        }

        keyboardHeight = e.keyboardHeight || e.detail.keyboardHeight;
        resizeLayout();
    }

    function onHideKeyboard() {
        if (ionic.Platform.isAndroid() && !ionic.Platform.isFullScreen) {
            return;
        }

        keyboardHeight = 0;
        resizeLayout();
    }

    $scope.$on('$destroy', function() {
        ionic.off('native.keyboardshow', onShowKeyboard, window);
        ionic.off('native.keyboardhide', onHideKeyboard, window);
    });
           
})

/*
<ion-item class="item-icon-right item-icon-left item-accordion" ng-class="{'item-calm': nav_id=='bot'}" ui-sref="tab.bot">
    <i class="icon icon-emoticon-smile"></i>
    <h2>アシスタント</h2>
    <p><i class="badge badge-danger" ng-show="bot_tasks.length > 0">{{bot_tasks.length}}</i></p>
    <i class="icon ion-chevron-right icon-accessory"></i>
</ion-item>
<ion-item class="item-remove-animate item-icon-right item-divider" type="item-text-wrap">
    <label ng-click="toggleGroup(0)"><i ng-class="groups[0] ? 'ion-minus' : 'ion-plus'"></i> ルーム</label>
    <button class="button button-icon icon ion-ios-plus-empty text-gray" ng-click="addMission(0)"></button>
</ion-item>


<ion-item class="item-remove-animate item-icon-right item-divider" type="item-text-wrap">
    <label ng-click="toggleGroup(2)"><i ng-class="groups[2] ? 'ion-minus' : 'ion-plus'"></i> メンバー</label>
</ion-item>
*/
.directive('chatRooms', function ($rootScope, $compile, $filter, $session, missionStorage, HPRIV) {
    var getTemplate = function(scope){
        var n = 0;
        var template = '';

        if ($rootScope.cur_home) {
            template += '<ion-item id="room_divider" class="item-remove-animate item-icon-right item-divider" type="item-text-wrap">';
            template += '    <label ng-click="toggleGroup(0)"><i ng-class="groups[0] ? \'ion-minus\' : \'ion-plus\'"></i> ルーム</label>';
            if ($rootScope.cur_home.priv == HPRIV.HMANAGER)        
                template += '    <button class="button button-icon icon ion-ios-plus-empty text-gray" ng-click="addMission(1)"></button>';
            template += '</ion-item>';

            len = $rootScope.missions.length;
            for (n = 0; n < len; n ++) {
                mission = $rootScope.missions[n];

                if (!(mission.private_flag == 0 || mission.private_flag == 1))
                    continue;

                template += missionStorage.mission_to_html(mission, scope.groups);
            }

            template += '<ion-item id="member_divider" class="item-remove-animate item-icon-right item-divider" type="item-text-wrap">';
            template += '    <label ng-click="toggleGroup(2)"><i ng-class="groups[2] ? \'ion-minus\' : \'ion-plus\'"></i> メンバー</label>';
            template += '</ion-item>';

            len = $rootScope.missions.length;
            for (n = 0; n < len; n ++) {
                mission = $rootScope.missions[n];

                if (!(mission.private_flag == 2 && mission.user_id != $session.user_id && mission.accepted == 1))
                    continue;

                template += missionStorage.mission_to_html(mission, scope.groups);
            }
        }
        template += '<div class="dummy-item"></div>';
        return template;
    }; 

    var linker = function(scope, element, attrs){
      element.html(getTemplate(scope));
      $compile(element.contents())(scope);
      $rootScope.$broadcast("rendered-missions");
    };

    return {
        restrict: "E",
        replace: true,
        link: linker
    };
});