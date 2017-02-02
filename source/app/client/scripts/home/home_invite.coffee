angular.module('app.home.invite', [])

.controller('homeInviteCtrl', 
    ($scope, $rootScope, $modalInstance, $api, home, email, 
        logger, $timeout, $session, homeStorage, $chat, ALERT_TYPE, $dialogs) ->
        content = $session.user_name + "様より、「ハンドクラウド」へ招待されました。\n" + 
            "招待されたグループは、下記の通りです。\n" +
            "グループ名:" + home.home_name + "\n"

        $scope.posting = false
        $scope.req =
            email: email
            content: content
            home_id: home.home_id
            signup_url: $api.base_url() + "#/signup"
            signin_url: $api.base_url() + "#/signin"

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')
            $api.hide_tutorial()

        $scope.ok = ->
            $scope.posting = true
            homeStorage.invite($scope.req, (res) ->
                $scope.posting = false
                if res.err_code == 0
                    if res.user_id != null
                        $chat.alert(ALERT_TYPE.INVITE_HOME, res.user_id, 
                            home_id: home.home_id
                            home_name: home.home_name
                        )
                    $rootScope.$broadcast('refresh-missions')
                    logger.logSuccess('グループに招待しました。')
                    $scope.cancel()
                else
                    logger.logError(res.err_msg)
            )
        
        $scope.canSubmit = ->
            return $scope.form_home_invite.$valid && !$scope.posting

        # 招待QRコード
        $scope.showInviteQR = () ->
            $scope.cancel()
            $dialogs.showQR($api.base_url() + "#/qr/home/" + home.home_id + "/" + home.invite_key, 
                "handcrowd://invite_home?id=" + home.home_id + "&key=" + home.invite_key,
                "招待QRコード(" + home.home_name + ")")
            return

        # チュートリアル
        if $session.tutorial
            $scope._destroy = $scope.$destroy
            $scope.$destroy = ->
                $api.hide_tutorial()
                $scope._destroy()

            $timeout(->
                # step 1
                $('#invite_email').tutpop(
                    placement: 'bottom'
                    title: 'メールアドレス'
                    content: '招待しようとするメールアドレスやユーザーIDを入力してください'
                ).tutpop('show').on('close.tutpop', $api.close_tutorial)
                # step 2
                $('#btn_invite_member_ok').tutpop(
                    placement: 'bottom'
                    title: 'グループへの招待'
                    content: '招待ボタンを押すと、招待メールが送付されます。'
                ).on('close.tutpop', $api.close_tutorial)
            , 500)

            $scope.onChange = ->
                if $scope.canSubmit()
                    $('#invite_email').tutpop('destroy')
                    $('#btn_invite_member_ok').tutpop('show')
                return

)