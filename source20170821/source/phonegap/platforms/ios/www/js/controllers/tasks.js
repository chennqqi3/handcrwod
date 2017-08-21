angular.module('app.controllers.tasks', [])

.controller('TasksCtrl', function($scope, $rootScope, $api, taskStorage) {
    // Refresh list of tasks
    $scope.$on('refresh-tasks', function(event) {
        taskStorage.search();
    });

    // Search
    $scope.$on('search-task', function(event) {
        
    });

    $rootScope.taskMode = 2;
    
    $scope.searchFilter = function(task) {
        var performer_id, search_string;
        if ($rootScope.taskMode === 1) {
            if (task.mission_id !== null) {
                return false;
            }
        } else if ($rootScope.taskMode === 2) {
            if (task.mission_id === null) {
                return false;
            }
            if ($rootScope.selectedMission) {
                if (task.mission_id !== $rootScope.selectedMission.mission_id) {
                    return false;
                }
            } else {
                return false;
            }
        } else if ($rootScope.taskMode === 3) {
            if ($rootScope.selectedMember) {
                performer_id = $rootScope.selectedMember.member_user_id;
                if (task.performer_id !== performer_id) {
                    return false;
                }
            } else {
                return false;
            }
        } else if ($rootScope.taskMode === 4) {
            if ($rootScope.calendar_date === null) {
                return false;
            }
        } else {
            if (task.priority === false) {
                return false;
            }
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
})

.controller('TaskDetailCtrl', function($scope, $stateParams, Chats) {
    $scope.chat = Chats.get($stateParams.chatId);
})