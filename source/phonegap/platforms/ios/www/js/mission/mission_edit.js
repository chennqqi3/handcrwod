'use strict'

angular.module('app.mission.edit', [])

.controller('missionEditCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, $ionicPopup, $ionicHistory,
        $ionicModal, $session, missionStorage, $timeout, logger, HPRIV) {
        $scope.ediatable = $rootScope.canEditMission();

        $scope.init = function() {
            $scope.mission_id = parseInt($stateParams.mission_id, 10);

            missionStorage.get($scope.mission_id, function(res) {
                if (res.err_code == 0) {
                    $scope.mission = res.mission;
                    $scope.mission.push_flag = $scope.mission.push_flag == 1;
                    $scope.mission.org_mission_name = $scope.mission.mission_name;
                    $scope.mission.org_push_flag = $scope.mission.push_flag;
                    $scope.mission.total_budget = 0;
                    $scope.mission.total_hours = 0;
                }
                else
                    logger.logError(res.err_msg)
            });
        }

        $scope.init();

        $scope.$on('$ionicView.beforeLeave', function() {
            if ($api.is_empty($scope.mission.mission_name))
                $scope.mission.mission_name = $scope.mission.org_mission_name;

            var mission = null;
            if ($scope.ediatable) {
                mission = $scope.mission
            }
            else if ($scope.mission.org_push_flag != $scope.mission.push_flag) {
                mission = {
                    'mission_id': $scope.mission.mission_id,
                    'push_flag': $scope.mission.push_flag
                }
            }

            if (mission) {
                missionStorage.edit(mission, function(data) {
                    if (data.err_code == 0) {
                        $rootScope.$broadcast('refresh-missions')
                    }
                    else
                        logger.logError(data.err_msg)
                }); 
            }           
        });

        $scope.canRemove = function() {
            if ($scope.mission == null)
                return false;

            switch($scope.mission.private_flag)
            {
                case 0:
                    return $rootScope.cur_home.priv == HPRIV.HMANAGER;
                case 1:
                    return $rootScope.cur_home.priv == HPRIV.HMANAGER || $rootScope.cur_home.priv == HPRIV.RMANAGER;            
            }
            return false;
        }

        $scope.remove = function() {
            var confirmPopup = $ionicPopup.confirm({
                title: 'チャットルーム削除',
                template: '「' + $rootScope.cur_mission.mission_name + '」を削除してもよろしいでしょうか？',
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            missionStorage.remove($rootScope.cur_mission, function(res) {
                                if (res.err_code == 0) {
                                    $session.setCurMission(null);
                                    $ionicHistory.nextViewOptions({
                                        historyRoot: true
                                    })
                                    $state.transitionTo('tab.chats');

                                    logger.logSuccess("チャットルームを削除しました。");
                                }
                                else {
                                    logger.logError(res.err_msg);
                                }
                            });
                        }
                    }
                ]
            });
        }
    }
);