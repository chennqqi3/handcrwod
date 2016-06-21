'use strict'

angular.module('app.activate', [])

# for toggle task edit panel
.controller('activateCtrl', 
    ($scope, $rootScope, $api, $location, $session, userStorage, AUTH_EVENTS, $auth, logger) ->
        param = $location.search()

        $scope.showMessage = false
        $scope.message = ""

        $auth.activate(param)
            .then((res) ->
                if res.err_code == 0
                    logger.logSuccess("ユーザー登録が完了しました。")
                    $rootScope.$broadcast(AUTH_EVENTS.loginSuccess)
                    $location.path('/home')
                else 
                    $scope.message = res.err_msg
                    
                $scope.showMessage = true
            )
)