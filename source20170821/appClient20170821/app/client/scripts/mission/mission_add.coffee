angular.module('app.mission.add', [])

.controller('missionAddCtrl', 
    ($scope, $session, $rootScope, $modalInstance, missionStorage, $api, logger, $location, $timeout) ->
        $scope.posting = false
        $scope.mission = 
            home_id: $rootScope.cur_home.home_id
            mission_name: ""
            private_flag: 1

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')
            $api.hide_tutorial()

        # Check privilege
        $scope.canSubmit = ->
            return $scope.form_mission_add.$valid

        # Add mission
        $scope.ok = ->
            if (!$scope.posting)
                $scope.posting = true
                missionStorage.add($scope.mission, (res) ->
                    if res.err_code == 0
                        $rootScope.$broadcast('refresh-missions', res.mission_id)
                        $scope.cancel()
                        logger.logSuccess('新しいチャットルームが作成されました。')
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
                $('#new_mission_name').tutpop(
                    placement: 'bottom'
                    title: 'ルーム名'
                    content: 'チャットルームには指定されたメンバーのみが利用可能な特定メンバー用ルームと、グループの全メンバーが利用可能な全メンバー用ルームがあります。作成しようとするルームのタイプを選択し、ルーム名を入力してください'
                ).tutpop('show').on('close.tutpop', $api.close_tutorial)
                # step 2
                $('#btn_add_mission_ok').tutpop(
                    placement: 'bottom'
                    title: 'チャットルーム新規登録'
                    content: '保存ボタンを押して、ルームを作成してください。'
                ).on('close.tutpop', $api.close_tutorial)
            , 500)

            $scope.onChange = ->
                if $scope.canSubmit()
                    $('#new_mission_name').tutpop('destroy')
                    $('#btn_add_mission_ok').tutpop('show')
                return
)