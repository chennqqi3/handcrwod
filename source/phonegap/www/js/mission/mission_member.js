angular.module('app.mission.member', [])

.controller('missionMemberCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, $ionicPopup,
        $ionicModal, $session, missionStorage, $timeout, logger, $ionicListDelegate) {
        $scope.show_search_member = false;
        $scope.search_member = { text: '' };

        $scope.init = function() {
            missionStorage.get($rootScope.cur_mission.mission_id, function(res) {
                if (res.err_code == 0)
                {
                    $scope.members = res.mission.members;
                }
            });
        }

        $rootScope.$on('invite-mission-member', function() {
            $scope.init();
        })

        // search
        $scope.open_search_member = function() {
            $scope.show_search_member = true;
            $rootScope.hide_navbar = true;
        }
        $scope.close_search_member = function() {
            $scope.show_search_member = false;
            $rootScope.hide_navbar = false;
            $scope.search_home.text = '';
        }
        
        $scope.removeMember = function(member) {
            var confirmPopup = $ionicPopup.confirm({
                title: 'メンバー削除',
                template: '「' + member.user_name + '」を削除してもよろしいでしょうか？',
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            missionStorage.remove_member($rootScope.cur_mission.mission_id, member.user_id, function(res) {
                                if (res.err_code == 0) {
                                    logger.logSuccess("メンバーをチャットルームから削除しました。");
                                    for (i=0; i < $scope.members.length; i ++) {
                                        if ($scope.members[i].user_id == member.user_id) {
                                            $scope.members.splice(i, 1);
                                            break;
                                        }
                                    }
                                }
                                else {
                                    logger.logError(res.err_msg);
                                }
                            });
                        }
                    }
                ]
            });
            confirmPopup.then(function(res) {
                $ionicListDelegate.closeOptionButtons();
            });
        };

        $scope.init();
    }
)

.controller('missionMemberAddCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, $ionicPopup, $ionicHistory,
        $ionicModal, $session, missionStorage, homeStorage, $timeout, logger, ALERT_TYPE, $chat) {
        $scope.show_search_member = false;
        $scope.search_member = { text: '' };

        mission_id = $rootScope.cur_mission.mission_id;
        mission_name = $rootScope.cur_mission.mission_name;
        $scope.content = $session.user_name + "( " + $session.email + " )様より、「ハンドクラウド」へ招待されました。\n" + 
            "招待されたチャットルームは、下記の通りです。\n" +
            "チャットルーム名:" + mission_name + "\n";

        $scope.init = function() {
            $scope.qr_image_url = $api.qr_image_url("https://www.handcrowd.com/app/#/qr/chat/" + mission_id + "/" + $rootScope.cur_mission.invite_key)
            missionStorage.invitable_members(mission_id, function(res){
                if (res.err_code == 0)
                    $scope.members = res.users;
                else
                    logger.logError(res.err_msg);
                return
            });
        }

        // search
        $scope.open_search_member = function() {
            $scope.show_search_member = true;
            $rootScope.hide_navbar = true;
        }
        $scope.close_search_member = function() {
            $scope.show_search_member = false;
            $rootScope.hide_navbar = false;
            $scope.search_home.text = '';
        }
            
        $scope.invite = function(email, content, closeModal) {
            $api.show_waiting();

            req = {
                email: email,
                content: content,
                mission_id: mission_id,
                signup_url: "https://www.handcrowd.com/app/#/signup",
                signin_url: "https://www.handcrowd.com/app/#/signin"
            }
                
            missionStorage.invite(req, function(res) {
                $api.hide_waiting();
                if (res.err_code == 0) {
                    if (res.user_id != null) {
                        $chat.alert(ALERT_TYPE.INVITE_HOME, res.user_id, {
                            home_id: $rootScope.cur_home.home_id,
                            home_name: $rootScope.cur_home.home_name,
                            mission_id: mission_id,
                            mission_name: mission_name
                        });
                    }
                    $rootScope.$broadcast('invite-mission-member');
                    logger.logSuccess('チャットルームに招待しました。');
                    if (closeModal) {                     
                        $scope.closeAddFromEmail();   
                        $timeout(function() {
                            $ionicHistory.goBack();  
                            $scope.close_search_member();  
                        })
                    }
                    else {
                        $ionicHistory.goBack();
                        $scope.close_search_member();
                    }
                    $rootScope.$broadcast("synced-server");
                }
                else
                    logger.logError(res.err_msg);
                return;
            });
            return;
        }

        // add from email
        $ionicModal.fromTemplateUrl('templates/mission/mission_member_add_mail.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalMemberAdd = modal;
        });

        $scope.addMemberFromEmail = function() {
            content = $session.user_name + "( " + $session.email + " )様より、「ハンドクラウド」へ招待されました。\n" + 
                "招待されたチャットルームは、下記の通りです。\n" +
                "チャットルーム名:" + mission_name + "\n";
            $scope.req = {
                email: '',
                content: content
            }
            $scope.modalMemberAdd.show();
        }

        $scope.closeAddFromEmail = function() {
            $scope.modalMemberAdd.hide();
        }

        $scope.init();
    }
);
