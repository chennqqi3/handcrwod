angular.module('app.member.list', [])

.controller('memberListCtrl', 
    function($rootScope, $scope, homeStorage, $ionicPopup, 
        logger, $session, $ionicModal, $api, ALERT_TYPE, $chat, $ionicListDelegate) {
        $scope.show_search_member = false;
        $scope.search_member = { text: '' };
        $scope.members = [];
        $scope.home_id = null;

        $scope.init = function(force) {
            if ($rootScope.cur_home != null) {
                if ($scope.home_id != $rootScope.cur_home.home_id || force) {
                    $scope.home_id = $rootScope.cur_home.home_id;
                    $scope.qr_image_url = $api.qr_image_url("https://www.handcrowd.com/app/#/qr/home/" + $scope.home_id + "/" + $rootScope.cur_home.invite_key)
                    $api.show_waiting();
                    homeStorage.members($rootScope.cur_home.home_id, function(res) {
                        $api.hide_waiting();
                        if (res.err_code == 0)
                            $scope.members = res.members;
                        else
                            $scope.members = [];
                    });
                }
            }
            else
                $scope.members = [];
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

        // add member
        $ionicModal.fromTemplateUrl('templates/member/member_add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalMemberAdd = modal;
        });

        $scope.addMember = function() {
            content = $session.user_name + "( " + $session.email + " )様より、「ハンドクラウド」へ招待されました。\n" + 
                    "招待されたグループは、下記の通りです。\n" +
                    "グループ名:" + $rootScope.cur_home.home_name + "\n";
            $scope.req = {
                email: '',
                content: content,
                home_id: $rootScope.cur_home.home_id,
                signup_url: "https://www.handcrowd.com/app/#/signup",
                signin_url: "https://www.handcrowd.com/app/#/signin"
            }
            $scope.modalMemberAdd.show();
        }

        $scope.canMemberAdd = function(form) {
            return form.$valid;
        }

        $scope.closeAdd = function() {
            $scope.modalMemberAdd.hide();
        }

        $scope.invite = function() {
            $api.show_waiting();
            homeStorage.invite($scope.req, function(res) {
                $api.hide_waiting();
                if (res.err_code == 0) {
                    if (res.user_id != null) {
                        $chat.alert(ALERT_TYPE.INVITE_HOME, res.user_id, {
                            home_id: $rootScope.cur_home.home_id,
                            home_name: $rootScope.cur_home.home_name
                        });

                        $scope.init(true);
                    }
                    logger.logSuccess('グループに招待しました。');
                    $scope.closeAdd();
                    $rootScope.$broadcast("synced-server");
                }
                else
                    logger.logError(res.err_msg);
            });
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
                            homeStorage.remove_member($rootScope.cur_home.home_id, member.user_id, function(res) {
                                if (res.err_code == 0) {
                                    logger.logSuccess("メンバーをグループから削除しました。");
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

        $scope.$on('select-home', function(event, new_mission_id) {
            $scope.init();
        });

        $scope.$on('$ionicView.loaded', function() {
            $scope.init();
        });    
    }
);