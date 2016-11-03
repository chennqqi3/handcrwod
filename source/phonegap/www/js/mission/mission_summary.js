'use strict'

angular.module('app.mission.summary', [])

.controller('missionSummaryCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, $ionicPopup, $ionicHistory,
        $ionicModal, $session, missionStorage, $timeout, logger, HPRIV) {
        $scope.editable = $rootScope.canEditMission();

        $scope.init = function() {
            $scope.mission_id = parseInt($stateParams.mission_id, 10);

            missionStorage.get($scope.mission_id, function(res) {
                if (res.err_code == 0) {
                    $scope.mission = res.mission;
                    $scope.mission.total_budget = 0;
                    $scope.mission.total_hours = 0;
                }
                else
                    logger.logError(res.err_msg)
            });
        }

        $scope.init();

        $scope.dirty = false;
        $scope.onChange = function() {
            $scope.dirty = true;
        }

        $scope.$on('$ionicView.beforeLeave', function() {
            if ($scope.dirty) {
                var confirmPopup = $ionicPopup.confirm({
                    title: '確認',
                    template: '概要を保存してもよろしいでしょうか？',
                    buttons: [
                        { text: 'キャンセル' },
                        {
                            text: '<b>OK</b>',
                            type: 'button-positive',
                            onTap: function(e) {
                                var mission = null;
                                if ($scope.editable) {
                                    mission = $scope.mission;

                                    if (mission) {
                                        missionStorage.edit(mission, function(data) {
                                            if (data.err_code == 0) {
                                                $rootScope.$broadcast('refresh-missions')
                                            }
                                            else
                                                logger.logError(data.err_msg)
                                        }); 
                                    }
                                }
                            }
                        }
                    ]
                });
                confirmPopup.then();
            }           
        });
    }
);