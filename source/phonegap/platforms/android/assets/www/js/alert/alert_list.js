angular.module('app.alert.list', [])

.controller('alertListCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, 
        $ionicModal, $session, taskStorage, $timeout, logger, $ionicPopup, userStorage, homeStorage) {

        $scope.init = function() {
            userStorage.alerts();
        }

        $scope.accept = function(alert, accept) {
            $api.show_waiting();
            homeStorage.accept_invite(alert.data.home_id, accept, function(res) {
                $api.hide_waiting();
                if (res.err_code == 0) {
                    if (accept == 1)
                        logger.logSuccess(alert.data.home_name + "への招待が完了しました。");
                    else
                        logger.logSuccess(alert.data.home_name + "への招待を取消しました。");

                    $rootScope.$broadcast('refresh-homes');
                    userStorage.refresh_alert_label();
                }
                else if (res.err_code == 61 || res.err_code == 63) {
                    logger.logError(res.err_msg);
                }
                else {
                    logger.logError(res.err_msg);
                    return;
                }

                for (i = 0; i < $rootScope.alerts.length; i ++)
                {
                    if ($rootScope.alerts[i].data.home_id == alert.data.home_id) {
                        $rootScope.alerts.splice(i, 1);
                    }
                }

                return;
            });
            return;
        }

        $scope.$on('$ionicView.beforeEnter', function() {
            $scope.init();
        });
    }
);
