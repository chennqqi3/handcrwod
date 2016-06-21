'use strict';
angular.module('app.signin', ['ionic'])

.controller('signinCtrl', 
    function($scope, $rootScope, CONFIG, $api, $auth, $location, userStorage,
        $state, AUTH_EVENTS, $session, $routeParams, logger, $syncServer, 
        $cordovaPush, $ionicSideMenuDelegate) {
        $ionicSideMenuDelegate.canDragContent(false);
        $scope.showSigninError = false
        $scope.showExpireError = false
        
        $scope.paramsFromStorage = function() {
            try {
                return JSON.parse(localStorage.getItem('signin_params') || {});
            } catch (err) {
                return {};
            }
        };
        
        $scope.paramsToStorage = function(params) {
            try {
                return localStorage.setItem('signin_params', JSON.stringify(params));
            } catch (err) {
                return {};
            }
        };
        
        $scope.canSubmit = function(form) {
            return form.$valid;
        };
        
        $scope.submitForm = function() {
            return $auth.login($scope.user).then(function(err_code) {
                return $scope.procLogin(err_code);
            });
        };

        $scope.procLogin = function(err_code) {
            if (err_code === 0) {
                $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                $state.go('tab.chats');
                userStorage.register_push();
                return $scope.paramsToStorage(null);
            } else if (err_code === 51) {
                $scope.showExpireError = true;
                return $scope.showSigninError = false;
            } else {
                $scope.showExpireError = false;
                return $scope.showSigninError = true;
            }
        }

        $scope.showSignin = true; 
        if ($routeParams.token !== void 0) {
            if ($routeParams.from === "facebook") {
                $scope.showSignin = false;
                $scope.params = $scope.paramsFromStorage();
                $auth.loginFacebook($routeParams.token)
                    .then(function(err_code) {
                        return $scope.procLogin(err_code);
                    });
            }
            else if ($routeParams.from === "google") {
                $scope.showSignin = false;
                $scope.params = $scope.paramsFromStorage();
                $auth.loginGoogle($routeParams.token)
                    .then(function(err_code) {
                        return $scope.procLogin(err_code);
                    });
            }
            else {
                $auth.autoLogin($routeParams.token, null, null);
            }
        }
        else {
            $scope.params = $location.search(), $scope.paramsToStorage($scope.params);
        }
        
        $scope.api_url = CONFIG.API_BASE;
        $scope.base_url = $api.base_url();
        $scope.facebook_redirect_url = "signin";
        $scope.facebook_signup_url = "signup_facebook";
        $scope.google_redirect_url = "signin";
        $scope.google_signup_url = "signup_google";

        $scope.user = {
            email: $session.email,
            password: ''
        }
        $scope.showSigninError = false;
        $scope.showExpireError = false;
    }
)

.controller('signoutCtrl', 
    function($scope, $rootScope, $state, $auth, AUTH_EVENTS, userStorage) {
        $scope.$on('$ionicView.enter', function() {
            userStorage.unregister_push();

            $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
            $auth.logout()
            $state.go('signin');
        });
    }
);
