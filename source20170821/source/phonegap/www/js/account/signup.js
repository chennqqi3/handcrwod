angular.module('app.signup', ['ionic'])

.controller('signupCtrl', 
    function($scope, $rootScope, CONFIG, $api, $auth, $location, $state, userStorage, $timeout,
        AUTH_EVENTS, $session, $routeParams, logger, $ionicModal, $http, $ionicSideMenuDelegate) {
        $ionicSideMenuDelegate.canDragContent(false);
        $scope.registered = 0;

        $scope.user = {
            app_mode: true,
            user_name: '',
            password: '',
            agree: true
        }

        $http.get("contract.txt")
            .success(function(data, status, headers, config) {
                $scope.contract = data;
            });

        $http.get("privacy.txt")
            .success(function(data, status, headers, config) {
                $scope.privacy = data;
            });

        $ionicModal.fromTemplateUrl('templates/account/contract.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalContract = modal;
        });
        $ionicModal.fromTemplateUrl('templates/account/contract.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalPrivacy = modal;
        });

        $scope.showContract = function() {
            $scope.title = "ハンドクラウド利用規約";
            $scope.content = $scope.contract;
            $scope.modalContract.show();
        }

        $scope.showPrivacy = function() {
            $scope.title = "個人情報の取り扱いについて";
            $scope.content = $scope.privacy;
            $scope.modalPrivacy.show();
        }

        $scope.close = function() {
            $scope.modalContract.hide();
            $scope.modalPrivacy.hide();
        }

        $scope.canSubmit = function(form) {
            return form.$valid;
        }
        
        $scope.paramsToStorage = function(params) {
            try {
                return localStorage.setItem('signin_params', JSON.stringify(params));
            } catch (err) {
                return {};
            }
        };

        $scope.submitForm = function() {
            $api.show_waiting();
            if ($scope.registered == 0) {
                $auth.signup($scope.user, function(res) {
                    $api.hide_waiting();
                    if (res.err_code == 0) {
                        //$scope.user.user_id = res.user_id;
                        //$scope.message = "ご登録いただいたメールアドレスへ、認証用メールが再送信されました。メール本文からURLをクリックして、登録を完了させてください。";
                        //$scope.registered = 1;
                        $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                        $timeout(function() {
                            $state.go('tab.chats');
                            userStorage.register_push();
                        }, 1000);
                        $scope.paramsToStorage(null);
                    }
                    else 
                        $scope.message = res.err_msg;

                    $scope.showMessage = true;
                });
            }
        }

        $scope.resendMail = function() {
            $api.show_waiting();
            if ($scope.registered == 1) {
                userStorage.resend_activate_mail($scope.user, function(res) {
                    $api.hide_waiting();
                    if (res.err_code == 0) {
                        $scope.message = "ご登録いただいたメールアドレスへ、認証用メールが送信されました。メール本文からURLをクリックして、登録を完了させてください。";
                        $scope.registered = 1;
                    }
                    else 
                        $scope.message = res.err_msg;
                        
                    $scope.showMessage = true;
                });
            }
        }

        $scope.start = function() {
            $auth.login($scope.user).then(function(err_code) {
                $state.go('tab.chats');
                userStorage.register_push();
            });
        }
    }
);