angular.module('app.home.menu', [])

.controller('homeMenuCtrl', 
    function ($scope, $rootScope, $api, AUTH_EVENTS, homeStorage, $session,
        $ionicModal, $ionicPopup, $ionicListDelegate, $ionicSideMenuDelegate, logger, $chat, $timeout) {
        $scope.show_search_home = false;
        $scope.search_home = { text: '' };

        // Refresh list of home
        $scope.$on('refresh-homes', function(event, new_home_id) {
            homeStorage.search()
                .then(function(homes) {
                });
        });

        // edit home
        $ionicModal.fromTemplateUrl('templates/home/home_edit.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalHomeEdit = modal;
        });
        $scope.edit = function(home) {
            home.org_home_name = home.home_name
            homeStorage.get(home.home_id, false, false, function(res) {
                if (res.err_code == 0) {
                    $scope.home = res.home;
                    $scope.home.org_home_name = $scope.home.home_name;
                    $scope.modalHomeEdit.show();
                }
                else {
                    logger.logError(data.err_msg);
                }
            });

            $ionicListDelegate.closeOptionButtons();
        };
        $scope.close = function() {
            if ($api.is_empty($scope.home.home_name))
                $scope.home.home_name = $scope.home.org_home_name;
            
            homeStorage.edit($scope.home, function(data) {
                if (data.err_code == 0) {
                    $rootScope.$broadcast('refresh-homes')
                }
                else
                    logger.logError(data.err_msg)
            });

            $scope.modalHomeEdit.hide();
        };
        // Upload logo
        $scope.onUploadLogo = function(files) {
            file = files[0];
            homeStorage.upload_logo($scope.home.home_id, file).progress( function(evt) {
                //console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
            }).success( function(data, status, headers, config) {
                if (data.err_code == 0) {
                    $scope.home.logo_url = data.logo_url;
                    home = homeStorage.get_home($scope.home.home_id);
                    home.logo_url = $scope.home.logo_url;
                    $chat.home('refresh-logo', $scope.home.home_id);
                }
                else
                    logger.logError(data.err_msg);
            });
        };

        $scope.onRemoveLogo = function() {
            homeStorage.remove_logo($scope.home.home_id, function(res) {
                if (res.err_code == 0) {
                    $scope.home.logo_url = res.logo_url;
                    home = homeStorage.get_home($scope.home.home_id);
                    home.logo_url = $scope.home.logo_url;
                    $chat.home('refresh-logo', $scope.home.home_id);
                }
                else
                    logger.logError(res.err_msg);
            });
            return;
        };

        $scope.$on('$destroy', function() {
            $scope.modalHomeEdit.remove();
        });
        $scope.$on('modal.hidden', function() {
            // Execute action
        });
        $scope.$on('modal.removed', function() {
            // Execute action
        });

        // add new home
        $scope.addHome = function() {
            $scope.home = {
                home_name: ""
            }

            // An elaborate, custom popup
            var popNewMission = $ionicPopup.show({
                template: '<input type="text" ng-model="home.home_name" placeholder="グループ名を入力してください。">',
                title: 'グループ新規登録',
                scope: $scope,
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            if (!$scope.home.home_name) {
                                e.preventDefault();
                            } else {
                                homeStorage.add($scope.home, function(res) {
                                    if (res.err_code == 0) {
                                        homeStorage.select($scope.home);

                                        $rootScope.$broadcast('refresh-homes', res.home.home_id);
                                        logger.logSuccess('新しいグループが作成されました。');
                                    }
                                    else
                                        logger.logError(res.err_msg);
                                });

                                return;
                            }
                        }
                    }
                ]
            });

            popNewMission.then(function(home) {
                
            });
        };

        $scope.removeHome = function(home) {
            var confirmPopup = $ionicPopup.confirm({
                title: 'グループ削除',
                template: '「' + home.home_name + '」を削除してもよろしいでしょうか？',
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            $timeout(function() {
                                $scope.removeHomeConfirm(home);  
                            });
                        }
                    }
                ]
            });
            confirmPopup.then(function(res) {
                $ionicListDelegate.closeOptionButtons();
            });
        }; 

        $scope.removeHomeConfirm = function(home) {
            var confirmPopup2 = $ionicPopup.confirm({
                title: 'グループ削除',
                template: 'グループを削除すると元に戻すことができなくなります。よろしいでしょうか？',
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            homeStorage.remove(home.home_id, function(res) {
                                if (res.err_code == 0) {
                                    logger.logSuccess("グループを削除しました。");
                                    $rootScope.$broadcast('refresh-homes');
                                    
                                    if ($rootScope.cur_home.home_id == home.home_id)
                                        homeStorage.set_cur_home(null);
                                }
                                else {
                                    logger.logError(res.err_msg);
                                }
                            });
                        }
                    }
                ]
            });
        };

        $scope.open = function(home) {
            homeStorage.select(home);

            $ionicSideMenuDelegate.toggleLeft();
        };

        // search
        $scope.open_search_home = function() {
            $scope.show_search_home = true;
        }
        $scope.close_search_home = function() {
            $scope.show_search_home = false;
            $scope.search_home.text = '';
        }

        $scope.logout = function(home) {
            $ionicSideMenuDelegate.toggleLeft();
        };
    }
)