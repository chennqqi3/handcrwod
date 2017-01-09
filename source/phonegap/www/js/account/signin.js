'use strict';
angular.module('app.signin', ['ionic'])

.controller('signinCtrl', 
    function($scope, $rootScope, CONFIG, $api, $auth, $location, userStorage,
        $state, AUTH_EVENTS, $session, $routeParams, logger, $syncServer, 
        $cordovaPush, $ionicSideMenuDelegate, $ionicLoading) {
        $ionicSideMenuDelegate.canDragContent(false);
        $scope.showSigninError = false
        $scope.showExpireError = false
        
        $scope.loginwithGoogle = function(){
            $ionicLoading.show({
              template: 'Logging in...'
            });

            window.plugins.googleplus.login(
              {},
              function (user_data) {
                // For the purpose of this example I will store user data on local storage
                // UserService.setUser({
                //   userID: user_data.userId,
                //   name: user_data.displayName,
                //   email: user_data.email,
                //   picture: user_data.imageUrl,
                //   accessToken: user_data.accessToken,
                //   idToken: user_data.idToken
                // });

                //alert(user_data.email);

                $ionicLoading.hide();
                $state.go('app.home');
              },
              function (msg) {
                $ionicLoading.hide();
              }
            );
        };

        var fbLoginSuccess = function(response) {
            if (!response.authResponse){
              fbLoginError("Cannot find the authResponse");
              return;
            }

            var authResponse = response.authResponse;

            getFacebookProfileInfo(authResponse)
            .then(function(profileInfo) {
              // For the purpose of this example I will store user data on local storage
              UserService.setUser({
                authResponse: authResponse,
                        userID: profileInfo.id,
                        name: profileInfo.name,
                        email: profileInfo.email,
                picture : "http://graph.facebook.com/" + authResponse.userID + "/picture?type=large"
              });
              $ionicLoading.hide();
              $state.go('app.home');
            }, function(fail){
              // Fail get profile info
              console.log('profile info fail', fail);
            });
        };

          // This is the fail callback from the login method
        var fbLoginError = function(error){
            console.log('fbLoginError', error);
            $ionicLoading.hide();
        };

        // This method is to get the user profile info from the facebook api
        var getFacebookProfileInfo = function (authResponse) {
            var info = $q.defer();

            facebookConnectPlugin.api('/me?fields=email,name&access_token=' + authResponse.accessToken, null,
              function (response) {
                        console.log(response);
                info.resolve(response);
              },
              function (response) {
                        console.log(response);
                info.reject(response);
              }
            );
            return info.promise;
        };

        $scope.loginwithFacebook = function() {
        
            facebookConnectPlugin.getLoginStatus(function(success){
              if(success.status === 'connected'){
                // alert('success');
                // The user is logged in and has authenticated your app, and response.authResponse supplies
                // the user's ID, a valid access token, a signed request, and the time the access token
                // and signed request each expire
                console.log('getLoginStatus', success.status);

                    // Check if we have our user saved
                    var user = UserService.getUser('facebook');

                    if(!user.userID){
                            getFacebookProfileInfo(success.authResponse)
                            .then(function(profileInfo) {
                                // For the purpose of this example I will store user data on local storage
                                UserService.setUser({
                                    authResponse: success.authResponse,
                                    userID: profileInfo.id,
                                    name: profileInfo.name,
                                    email: profileInfo.email,
                                    picture : "http://graph.facebook.com/" + success.authResponse.userID + "/picture?type=large"
                                });

                                $state.go('app.home');
                            }, function(fail){
                                // Fail get profile info
                                console.log('profile info fail', fail);
                            });
                        }else{
                            $state.go('app.home');
                        }
                } else {
                // If (success.status === 'not_authorized') the user is logged in to Facebook,
                        // but has not authenticated your app
                // Else the person is not logged into Facebook,
                        // so we're not sure if they are logged into this app or not.

                        console.log('getLoginStatus', success.status);

                        $ionicLoading.show({
                            template: 'Logging in...'
                        });

                        // Ask the permissions you need. You can learn more about
                        // FB permissions here: https://developers.facebook.com/docs/facebook-login/permissions/v2.4
                    facebookConnectPlugin.login(['email', 'public_profile'], fbLoginSuccess, fbLoginError);
                }
            });
          };
 

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
