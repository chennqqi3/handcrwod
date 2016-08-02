'use strict'

angular.module('app.task.star', [])

.controller('taskStarCtrl', 
    ($scope, $api, taskStorage, missionStorage, filterFilter, HPRIV,
        $rootScope, $routeParams, logger, $session, $dateutil, $timeout, $dialogs) ->
        $rootScope.nav_id = "task_star"
        missionStorage.select_mission_in_nav()
        
        # Initialize
        $scope.sync = (init_offset) ->
            if init_offset
                # init offset for complete
                if $rootScope.cur_home != null
                    home_id = $rootScope.cur_home.home_id
                offset_key = 'home_' + home_id
                taskStorage.set_completed_offset(offset_key, 0)
            taskStorage.search(undefined, 1).then((tasks) ->
            )

        $scope.sync(true)

        $scope.$on("synced-server", ->
            $scope.sync()
        )

        # Refresh list of tasks
        $scope.$on('refresh-tasks', (event) ->
        )

        # Search
        $scope.$on('search-task', (event) ->
            #$scope.refreshSearchFilter()
        )

        # change home
        $scope.$on('select-home', (event) ->
            $scope.sync(true)
        )

        $scope.searchFilter = (task) ->
            if task.priority != true
                return false

            return true
            
        $scope.searchFilter1 = (task) ->
            searched = $scope.searchFilter(task)
            if searched
                if task.complete_flag == false
                    return true

            return false
            
        $scope.searchFilter2 = (task) ->
            searched = $scope.searchFilter(task)
            if searched
                if task.complete_flag == true
                    return true

            return false

        # Search tasks by mission
        $scope.$on('select-mission', (event) ->
            taskStorage.refresh_remaining()
        )

        # Search completed
        $scope.loadCompleted = ->
            taskStorage.search_completed(undefined, 1)

        # Complete task
        $scope.canComplete = (task) ->
            return task.performer_id == $session.user_id

        $scope.completed = (task) ->
            task.progress = 100 if task.complete_flag
            task.progress = 0 if !task.complete_flag
            if task.performer_id == $session.user_id
                taskStorage.refresh_remaining()
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, complete_flag: task.complete_flag, progress: task.progress }, (res) ->
                if res.err_code == 0
                    if task.complete_flag
                        logger.logSuccess('タスクが完了されました。')
                        $(".good_job").removeClass('good_job_show').show()
                        $timeout( -> 
                            $(".good_job").addClass('good_job_show')
                            $timeout( -> 
                                $(".good_job").hide()
                            , 1500)
                        , 10)
                else
                    logger.logError(res.err_msg)
            )
            return

        # Select performer
        $scope.selPerformer = (task) ->
            if $rootScope.canEditTask()
                $dialogs.selPerformer(task)

        # Hire from modal performer
        $scope.$on('complete_task', (event, task) ->
            $scope.completed(task)
        )

        # Change progress of task
        $scope.changeProgress = (task) ->
            taskStorage.edit({ task_id: task.task_id, progress: task.progress })

        # Change priority of task
        $scope.checkPriority = (task) ->
            taskStorage.edit({ task_id: task.task_id, priority: task.priority })
            taskStorage.refresh_remaining()
  
        # Select task
        $scope.selectTask = (task) ->
            # select item
            if $scope.selectedTask == task
                $scope.selectedTask = null
            else
                $scope.selectedTask = task
            $rootScope.$broadcast('select-task', $scope.selectedTask)

        $scope.$on('select-task', (event, task) -> 
            # click close button from edit panel
            if task == null and $scope.selectedTask != null
                $scope.selectedTask = null
        )

        $scope.is_past = (task) ->
            return $dateutil.is_past(task.plan_end_date)

        $scope.addTask = () ->
            $dialogs.addTask($rootScope.cur_mission)

)