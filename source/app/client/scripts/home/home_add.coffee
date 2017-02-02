angular.module('app.home.add', [])

.controller('homeAddCtrl', 
    ($scope, $rootScope, $modalInstance, homeStorage, $api, logger, $session, $timeout) ->
        $scope.posting = false
        $scope.home = 
            home_name: ""

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')
            $api.hide_tutorial()

        # Check privilege
        $scope.canSubmit = ->
            return $scope.form_home_add.$valid

        # Add home
        $scope.ok = ->
            if (!$scope.posting)
                $scope.posting = true
                homeStorage.add($scope.home, (res) ->
                    if res.err_code == 0
                        $rootScope.$broadcast('added_home', res.home)
                        $scope.cancel()
                        logger.logSuccess('新しいグループが作成されました。')
                    else
                        logger.logError(res.err_msg)
                    $scope.posting = false
                )

        # チュートリアル
        if $session.tutorial
            $scope._destroy = $scope.$destroy
            $scope.$destroy = ->
                $api.hide_tutorial()
                $scope._destroy()

            $timeout(->
                # step 1
                $('#new_home_name').tutpop(
                    placement: 'bottom'
                    title: 'グループ名'
                    content: 'グループ名を入力してください'
                ).tutpop('show').on('close.tutpop', $api.close_tutorial)
                # step 2
                $('#btn_add_home_ok').tutpop(
                    placement: 'bottom'
                    title: 'グループ新規登録'
                    content: '保存ボタンをクリックして、グループの作成を完了してください。'
                ).on('close.tutpop', $api.close_tutorial)
            , 500)

            $scope.onChange = ->
                if $scope.canSubmit()
                    $('#new_home_name').tutpop('destroy')
                    $('#btn_add_home_ok').tutpop('show')
                return
)