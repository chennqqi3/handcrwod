'use strict'

angular.module('app.signin', [])

# for toggle task edit panel
.controller('signinCtrl', 
    ($scope, $rootScope, CONFIG, $api, $auth, $location, AUTH_EVENTS, $session, $routeParams, logger, chatStorage) ->
        chatStorage.refresh_unreads_title()
        $scope.canSubmit = ->
            return $scope.form_signin.$valid

        $scope.submitForm = ->
             $auth.login($scope.user)
                .then((err_code) ->
                    $scope.procLogin(err_code)
                )
        
        $scope.procLogin = (err_code) ->
            if err_code == 0
                $rootScope.$broadcast(AUTH_EVENTS.loginSuccess)
                if $rootScope.redirect_url != undefined
                    url = $rootScope.redirect_url
                    $rootScope.redirect_url = undefined
                    $location.path(url)
                else
                    $location.path('/home')
                $session.signinParamsToStorage(null)
            else if err_code == 51
                $scope.showExpireError = true
                $scope.showSigninError = false
            else
                $scope.showExpireError = false
                $scope.showSigninError = true

        $scope.showSignin = true

        if $routeParams.token != undefined
            if $routeParams.from == "facebook"
                $scope.showSignin = false
                $scope.params = $session.signinParamsFromStorage()
                $auth.loginFacebook($routeParams.token)
                    .then((err_code) ->
                        $scope.procLogin(err_code)
                    )
            else if $routeParams.from == "google"
                $scope.showSignin = false
                $scope.params = $session.signinParamsFromStorage()
                $auth.loginGoogle($routeParams.token)
                    .then((err_code) ->
                        $scope.procLogin(err_code)
                    )
            else
                $auth.autoLogin($routeParams.token, null, null)
        else
            $scope.params = $location.search()
            $session.signinParamsToStorage($scope.params)

        $scope.api_url = CONFIG.API_BASE
        $scope.base_url = $api.base_url()
        $scope.facebook_redirect_url = "signin"
        $scope.facebook_signup_url = "signup_facebook"
        $scope.google_redirect_url = "signin"
        $scope.google_signup_url = "signup_google"

        $scope.user =
            login_id: $session.login_id
            password: ''

        $scope.showSigninError = false
        $scope.showExpireError = false
)