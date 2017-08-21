'use strict'

angular.module('app.forgotpwd', [])

# for toggle task edit panel
.controller('forgotpwdCtrl', 
    ($scope, $api, $location) ->
        reset_url = $api.base_url() + "#/resetpwd"

        $scope.user =
            email: ''
            reset_url: reset_url

        $scope.showMessage = false

        $scope.canSubmit = ->
            return $scope.form_forgotpwd.$valid

        $scope.submitForm = ->
             $api.call("user/send_reset_password", $scope.user)
                .success((data, status, headers, config) ->
                    if data.err_code == 0
                        $scope.message = "パスワードリセット用メールをお客様のメールアドレスへ届けました。メールからリンクをクリックして、パスワードをリセットしてください。"
                    else 
                        $scope.message = data.err_msg
                        
                    $scope.showMessage = true
                )
)