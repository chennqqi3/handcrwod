'use strict'

angular.module('app.signup_facebook', [])

# for toggle task edit panel
.controller('signupFacebookCtrl', 
    ($scope, $api, $auth, $location, $dialogs, $http, $routeParams, $session) ->
        param = $location.search()

        activate_url = $api.base_url() + "#/activate"

        $scope.user =
            user_name: ''
            email: ''

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

        $http.get("contract.txt")
            .success((data, status, headers, config) ->
                $scope.contract = data
            )
        $http.get("privacy.txt")
            .success((data, status, headers, config) ->
                $scope.privacy = data
            )

        $scope.canSubmit = ->
            return $scope.form_signup.$valid

        $scope.submitForm = ->
            $api.call("facebook/register", {
                    token: $routeParams.token
                    email: $scope.user.email
                })
                .success((data, status, headers, config) ->
                    if data.err_code == 0
                        $session.create(data)
                        $location.path('/home')
                    else 
                        $scope.message = data.err_msg
                        $scope.showMessage = true
                )

        $scope.showContract = ->
            $dialogs.showContract("ハンドクラウド利用規約", $scope.contract)

        $scope.showPrivacy = ->
            $dialogs.showContract("ハンドクラウドサービス　個人情報の取り扱いについて", $scope.privacy)
)