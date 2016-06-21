'use strict'

angular.module('app.signin', [])

# for toggle task edit panel
.controller('signinCtrl', 
    ($scope, $rootScope, CONFIG, $api, $auth, $location, AUTH_EVENTS, $session, $routeParams, logger, $syncServer, chatStorage, homeStorage, missionStorage) ->
        chatStorage.refresh_unreads_title()        

        $scope.paramsFromStorage = ->
            try 
                return JSON.parse(localStorage.getItem('signin_params') || {})
            catch err
                return {}

        $scope.paramsToStorage = (params)->
            try 
                localStorage.setItem('signin_params', JSON.stringify(params))                
            catch err
                return {}

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
                $location.path('/home')
                $scope.paramsToStorage(null)
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
                $scope.params = $scope.paramsFromStorage()
                $auth.loginFacebook($routeParams.token)
                    .then((err_code) ->
                        $scope.procLogin(err_code)
                    )
            else if $routeParams.from == "google"
                $scope.showSignin = false
                $scope.params = $scope.paramsFromStorage()
                $auth.loginGoogle($routeParams.token)
                    .then((err_code) ->
                        $scope.procLogin(err_code)
                    )
            else
                $auth.autoLogin($routeParams.token, null, null)
        else
            $scope.params = $location.search()
            $scope.paramsToStorage($scope.params)

        $scope.api_url = CONFIG.API_BASE
        $scope.base_url = $api.base_url()
        $scope.facebook_redirect_url = "signin"
        $scope.facebook_signup_url = "signup_facebook"
        $scope.google_redirect_url = "signin"
        $scope.google_signup_url = "signup_google"

        $scope.user =
            email: $session.email
            password: ''

        $scope.showSigninError = false
        $scope.showExpireError = false
)