angular.module('app.chat.list', [])

.controller('chatsCtrl', 
    function($scope, $rootScope, $session, $ionicPopup, $ionicModal, $ionicListDelegate, $ionicScrollDelegate, $ionicActionSheet, missionStorage, chatStorage, logger, $timeout, $state) {

    // Search
    $scope.roomFilter = function(mission) {
        return mission.private_flag == 0 || mission.private_flag == 1;
    }

    $scope.memberFilter = function(mission) {
        return mission.private_flag == 2 && mission.user_id != $session.user_id
    }

    // toggle group
    $scope.groups = [true, true, true];
    $scope.toggleGroup = function(index) {
        $scope.groups[index] = !$scope.groups[index];
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
    $scope.open_search_chat = function() {
        $scope.show_search_chat = false;
        $rootScope.hide_navbar = false;

        $scope.search.text = "";
        $scope.init_search_result();
        $scope.modalChatSearch.show();        
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
        chatStorage.search_messages($rootScope.cur_home.home_id, null, search_string, $scope.prev_id, $scope.next_id).then(function(messages) {
            if (messages.length > 0) {
                if ($scope.next_id !== null) {
                    for (var i = 0; i < messages.length; i++)
                        $scope.messages.push(messages[i]);
                    if($scope.messages.length > MAX_MSG_LENGTH)
                        $scope.messages.splice(0, $scope.messages.length - MAX_MSG_LENGTH);
                    $scope.startScrollTimer($scope.next_id, "next");
                } else if ($scope.prev_id !== null) {
                    for (var i = 0; i<messages.length; i++)
                        $scope.messages.splice(i, 0, messages[i]);
                    if($scope.messages.length > MAX_MSG_LENGTH)
                        $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length-MAX_MSG_LENGTH);
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
    };

    $scope.$on('$destroy', function() {
        $scope.modalChatSearch.remove();
        $scope.init_search_result();
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
            template: '<ion-list><ion-radio ng-value="0" ng-model="mission.private_flag">全メンバー用</ion-radio>' + 
                '<ion-radio ng-value="1" ng-model="mission.private_flag">特定メンバー用</ion-radio>' +
                '<label class="item item-input"><input type="text" ng-model="mission.mission_name" required placeholder="ルーム名を入力してください。"></label>' + 
                '</ion-list>',
            title: 'チャットルームを新規作成します。',
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
                missionStorage.add(mission, function(res) {
                    if (res.err_code == 0) {
                        $rootScope.$broadcast('refresh-missions', res.mission_id);
                        logger.logSuccess('新しいチャットルームが作成されました。');
                    }
                    else
                        logger.logError(res.err_msg);
                });
            }
        });
        return;
    }

    $scope.openMission = function(private_flag) {
        $dialogs.openMission(private_flag);
        return;
    }

    $scope.pinMission = function(mission) {
        if (mission.pinned == 1)
            pinned = 0;
        else
            pinned = 1;

        missionStorage.pin(mission.mission_id, pinned, function(res) {
            if (res.err_code == 0)
                $rootScope.$broadcast('refresh-missions');
            else
                logger.logError(res.err_msg);

        });
        $ionicListDelegate.closeOptionButtons();
        return;
    }

    // Refresh list of missions
    $scope.init = function() {
        if ($rootScope.cur_home != null) {
            missionStorage.search($rootScope.cur_home.home_id)
                .then(function(missions) {

                });
        }
    }

    $scope.$on('refresh-missions', function(event, new_mission_id) {
        $scope.init();
    });

    $scope.$on('refresh-homes', function(event, new_mission_id) {
        $scope.init();
    });

    $scope.$on('select-home', function(event, new_mission_id) {
        $scope.init();
    });

    $scope.$on('synced-server', function(event) {
        $scope.init();
    });

    $scope.$on('$ionicView.beforeEnter', function(event) {
        $scope.init();
    });

    $scope.open_member = function(mission) {
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
    };
           
});