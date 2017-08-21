'use strict'

angular.module('app.resetpwd', [])

# for toggle task edit panel
.controller('resetpwdCtrl', 
    ($scope, $api, $location, $timeout) ->
        param = $location.search()

        $scope.user =
            user_id: param.user_id
            activate_key: param.activate_key
            password: ''

        $scope.showMessage = false

        $scope.canSubmit = ->
            return $scope.form_resetpwd.$valid

        $scope.submitForm = ->
             $api.call("user/reset_password", $scope.user)
                .success((data, status, headers, config) ->
                    if data.err_code == 0
                        $scope.message = "パスワードがリセットされました。"
                        $timeout(->
                            $location.path('/signin')
                        , 1000)
                    else 
                        $scope.message = data.err_msg
                        
                    $scope.showMessage = true
                )
)