'use strict'

angular.module('app.signup_facebook', [])

# for toggle task edit panel
.controller('signupFacebookCtrl', 
    ($scope, $api, $auth, $location, $dialogs, $http, $routeParams, $session, AUTH_EVENTS, $rootScope) ->
        param = $location.search()

        activate_url = $api.base_url() + "#/activate"

        $scope.user =
            login_id: ''
            user_name: ''

        $api.call("facebook/get_info", {
                token: $routeParams.token
            })
            .success((data, status, headers, config) ->
                if data.err_code == 0
                    $scope.user.facebook_id = data.facebook_id
                    $scope.user.user_name = data.user_name
                else 
                    $scope.message = data.err_msg
                    $scope.showMessage = true
            )

        $scope.showMessage = false
        $scope.message = ""
        $scope.registered = false

        $http.get("contract.txt")
            .success((data, status, headers, config) ->
                $scope.contract = data
            )
        $http.get("privacy.txt")
            .success((data, status, headers, config) ->
                $scope.privacy = data
            )

        $scope.canSubmit = ->
            return $scope.form_signup.$valid && $scope.registered==false && $scope.posting == false && !_is_empty($scope.user.facebook_id)

        $scope.submitForm = ->
            $scope.show_error = true

            return if $scope.form_signup.$valid == false
            
            $scope.posting = true
            $auth.signupFacebook({
                    token: $routeParams.token
                    login_id: $scope.user.login_id
                    user_name: $scope.user.user_name
                }, (res) ->
                $scope.posting = false
                if res.err_code == 0
                    $rootScope.$broadcast(AUTH_EVENTS.loginSuccess)
                    $location.path('/home')
                else 
                    $scope.message = res.err_msg
                    $scope.showMessage = true
            )

        $scope.showContract = ->
            $dialogs.showContract("ハンドクラウド利用規約", $scope.contract)

        $scope.showPrivacy = ->
            $dialogs.showContract("ハンドクラウドサービス　個人情報の取り扱いについて", $scope.privacy)
)