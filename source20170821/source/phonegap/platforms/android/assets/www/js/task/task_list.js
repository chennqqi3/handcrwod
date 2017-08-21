angular.module('app.task.list', [])

.controller('taskListCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, 
        $ionicModal, $session, taskStorage, $timeout, logger, $ionicPopup) {
        $scope.home_id = null;
        $scope.mission_id = undefined;

        $scope.init = function(force_init) {
            if ($rootScope.cur_home != null) {
                if ($stateParams.mission_id != undefined)
                    $scope.mission_id = parseInt($stateParams.mission_id, 10);
                else {
                    $scope.mission_id = undefined;
                    $rootScope.taskMode = 3;
                }

                if ($rootScope.task_mission_id != $scope.mission_id || $scope.home_id != $rootScope.cur_home.home_id || force_init) {
                    $rootScope.task_mission_id = $scope.mission_id;
                    $scope.home_id = $rootScope.cur_home.home_id;
                    $api.show_waiting();
                    taskStorage.search($rootScope.task_mission_id)
                        .then(function(tasks){
                            $api.hide_waiting();
                        });
                }
            }
        }

        $scope.searchFilter = function(task) {
            var performer_id, search_string;
            if ($rootScope.taskMode === 1) {
                if (task.mission_id !== null) {
                    return false;
                }
            } else if ($rootScope.taskMode === 2) { /// mission mode
                if (task.mission_id === null) {
                    return false;
                }
                if ($rootScope.cur_mission) {
                    if (task.mission_id !== $rootScope.task_mission_id) {
                        return false;
                    }
                } else {
                    return false;
                }
            } else { // $rootScope.taskMode === 3 home mode


            }

            if ($rootScope.task_search_string) {
                search_string = $rootScope.task_search_string.toLowerCase();
                if (search_string !== "" && task.task_name.toLowerCase().indexOf(search_string) === -1) {
                    return false;
                }
            }
            return true;
        };

        $scope.searchFilter1 = function(task) {
            var searched;
            searched = $scope.searchFilter(task);
            if (searched) {
                if (task.complete_flag === false && task.processed === 0) {
                    return true;
                }
            }
            return false;
        };

        $scope.searchFilter2 = function(task) {
            var searched;
            searched = $scope.searchFilter(task);
            if (searched) {
                if (task.complete_flag === false && task.processed === 1) {
                    return true;
                }
            }
            return false;
        };

        $scope.searchFilter3 = function(task) {
            var searched;
            searched = $scope.searchFilter(task);
            if (searched) {
                if (task.complete_flag === true) {
                    return true;
                }
            }
            return false;
        };            

        // Search completed
        $scope.loadCompleted = function() {
            taskStorage.search_completed();
        }

        // Complete task
        $scope.canComplete = function(task) {
            return task.performer_id == $session.user_id;
        }

        $scope.add = function() {
            $scope.task = {
                mission_id: $scope.mission_id,
                task_name: ""
            }

            // An elaborate, custom popup
            var popNewTask = $ionicPopup.show({
                template: '<input type="text" ng-model="task.task_name" placeholder="タスク名を入力してください。">',
                title: 'タスク新規登録',
                scope: $scope,
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            if (!$scope.task.task_name) {
                                e.preventDefault();
                            } else {
                                taskStorage.add($scope.task, function(res) {
                                    if (res.err_code == 0) {
                                        $scope.init(true);
                                        logger.logSuccess('新しいタスクが作成されました。');
                                    }
                                    else
                                        logger.logError(res.err_msg);
                                });

                                return;
                            }
                        }
                    }
                ]
            });

            popNewTask.then(function() {
                
            });

        }

        $scope.remove = function(task) {
            var confirmPopup = $ionicPopup.confirm({
                title: 'タスク削除',
                template: '「' + task.task_name + '」を削除してもよろしいでしょうか？',
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            taskStorage.remove(task, function(res) {
                                if (res.err_code == 0) {
                                    logger.logSuccess("タスクを削除しました。");
                                    for (var i=0; i < $scope.tasks.length - 1; i ++) {
                                        if ($rootScope.tasks[i].task_id == task.task_id) {
                                            $scope.tasks.splice(i, 1);
                                            break;
                                        }
                                    }
                                }
                                else {
                                    logger.logError(res.err_msg);
                                }
                            });
                        }
                    }
                ]
            });
            confirmPopup.then(function(res) {
                $ionicListDelegate.closeOptionButtons();
            });
        };

        $scope.$on('select-home', function(event, new_mission_id) {
            $scope.init();
        });

        $scope.$on('$ionicView.beforeEnter', function() {
            $scope.init();
        });
    }
);
