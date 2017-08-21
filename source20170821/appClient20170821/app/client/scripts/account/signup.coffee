'use strict'

angular.module('app.signup', [])

# for toggle task edit panel
.controller('signupCtrl', 
    ($scope, $api, $location, $auth, $session, $rootScope, $dialogs, $http, userStorage, AUTH_EVENTS, $timeout) ->
        param = $location.search()
        $scope.posting = false

        activate_url = $api.base_url() + "#/activate"

        $scope.email_readonly = !$api.is_empty(param.email)

        $scope.user =
            login_id: ''
            user_name: ''
            email: param.email
            password: ''
            activate_url: activate_url
            invite_home_id: param.invite_home_id
            invite_mission_id: param.invite_mission_id
            key: param.key

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
            if !$scope.registered
                $auth.signup($scope.user, (res) ->
                    $scope.posting = false
                    $scope.err_code = res.err_code
                    if res.err_code == 0
                        #$scope.message = "仮登録が完了しました（※まだ登録は完了していません）。認証用のメール本文から認証用リンクをクリックして、本登録を行ってください。"
                        $rootScope.$broadcast(AUTH_EVENTS.loginSuccess)
                        $timeout(->
                            $location.path('/home')
                        , 1000)
                        $session.signinParamsToStorage(null)

                        $scope.registered = true
                    else 
                        $scope.message = res.err_msg
                        $scope.showMessage = true
                )
            ###
                userStorage.resend_activate_mail($scope.user, (res) ->
                    $scope.posting = false
                    $scope.err_code = res.err_code
                    if res.err_code == 0
                        $scope.message = "ご登録いただいたメールアドレスへ、認証用メールが再送信されました。認証用のメール本文から認証用リンクをクリックして、本登録を行ってください。"
                        $scope.registered = true
                    else 
                        $scope.message = res.err_msg
                        
                    $scope.showMessage = true
                )
            else
            ###

        $scope.showContract = ->
            $dialogs.showContract("ハンドクラウド利用規約", $scope.contract)

        $scope.showPrivacy = ->
            $dialogs.showContract("ハンドクラウドサービス　個人情報の取り扱いについて", $scope.privacy)
)