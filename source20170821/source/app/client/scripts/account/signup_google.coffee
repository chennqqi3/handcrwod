'use strict'

angular.module('app.signup_google', [])

# for toggle task edit panel
.controller('signupGoogleCtrl', 
    ($scope, $api, $auth, $location, $dialogs, $http, $routeParams, $session) ->
        param = $location.search()

        activate_url = $api.base_url() + "#/activate"

        $scope.user =
            user_name: ''
            email: ''

        $api.call("google/get_info", {
                token: $routeParams.token
            })
            .success((data, status, headers, config) ->
                if data.err_code == 0
                    $scope.user.google_id = data.google_id
                    $scope.user.user_name = data.user_name
                    $scope.user.email = data.email
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
            return $scope.form_signup.$valid && $scope.registered==false && $scope.posting == false

        $scope.submitForm = ->
            $scope.show_error = true

            return if $scope.form_signup.$valid == false
            
            $scope.posting = true
            $api.call("google/register", {
                    token: $routeParams.token
                    email: $scope.user.email
                })
                .success((data, status, headers, config) ->
                    $scope.posting = false
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