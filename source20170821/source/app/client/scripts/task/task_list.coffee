'use strict'

angular.module('app.task.list', [])

.directive('progressBack', 
    ($timeout, $rootScope, $parse, $api) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    progress = ngModel.$modelValue
                    progress = 0 if progress == null or progress == undefined
                    $(element).css("width", progress + "%")
                
                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.directive('completeHandle', 
    ($timeout, $rootScope, $parse, $api, $session) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    task = ngModel.$modelValue
                    refresh = ()->
                        task = ngModel.$modelValue
                        pr = task.progress
                        pr = 0 if pr == null or pr == undefined or pr == 100
                        $(element).parents('.task-item').find('.progress').css('width', pr + '%')

                    scope.$on('refresh-tasks', (event) ->
                        refresh()
                    )
                    scope.$on('refresh-progress', (event, t) ->
                        if t.task_id == task.task_id
                            task.progress = t.progress
                            refresh()
                    )
                
                    enableDrag = ->
                        task = ngModel.$modelValue
                        if task.performer_id != $session.user_id
                            $(element).draggable('disable')
                        else
                            $(element).draggable('enable')

                    scope.$watch('task.performer_id', (newVal, oldVal) ->
                        enableDrag()
                        return
                    )

                    $(element).draggable(
                        revert: true
                        axis: 'x'
                        start: ->
                        drag: ->
                            x = $(this).position().left
                            w = $('.task-list').width()
                            s = 50
                            e = w - 40

                            if x < s
                                pr = 0
                            else if x > e
                                pr = 100
                            else
                                pr = parseInt( (x - s) / (e - s) * 100)

                            task = ngModel.$modelValue
                            task.progress = pr
                            $(this).parents('.task-item').find('.progress').css('width', pr + '%')
                            
                        stop: ->
                            task = ngModel.$modelValue
                            if task.progress == 100
                                task.complete_flag = true
                                scope.completed(task)
                                scope.$apply()
                            else
                                scope.changeProgress(task)

                            $(element).removeAttr( 'style' );
                    )
                    
                    enableDrag()

                ###
                draw = (task) ->
                    canvas = $(element).find('.progress-arc')[0]
                    context = canvas.getContext('2d') if canvas != null
                    context.clearRect(0, 0, 30, 30)
                    context.beginPath();
                    context.arc(15, 15, 14, 0, 2 * Math.PI);
                    if back == true
                        context.fillStyle = '#fff';
                        context.fill();
                    context.lineWidth = 2
                    if task.processed == 1 and task.complete_flag == false
                        context.strokeStyle = '#428bca'
                    else
                        context.strokeStyle = '#ccc'
                    context.stroke();
                    return if task.progress == null or task.progress == undefined or task.progress == 0 or task.complete_flag == true
                    context.beginPath();
                    context.arc(15, 15, 14, - Math.PI / 2, 2 * Math.PI * task.progress / 100 - Math.PI / 2);
                    context.lineWidth = 2
                    context.strokeStyle = '#d9534f'
                    context.stroke();
                    
                    if back == true
                        context.font = '10pt';
                        context.lineWidth = 1;
                        context.textAlign = 'center';
                        context.strokeStyle = '#d9534f';
                        context.strokeText(task.progress + '%', 15, 18);
                ###

                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.directive('taskSortable', 
    ($rootScope, taskStorage, $filter, $api, logger) ->
        o_sorts = []
        return {
            restrict: 'A'
            link: (scope, ele, attrs) ->
                $(ele).sortable( 
                    distance: 30
                    axis: 'y'
                    handle: '.task-name a'
                    start: (event, ui) ->
                        o_sorts = []
                        $('.task-sort .task-item').each( ->
                            task_id = $(this).data("taskId")
                            o_sorts.push(task_id) if task_id != undefined
                        )
                    update: (event, ui) ->
                        n_sorts = []
                        $('.task-sort .task-item').each( ->
                            task_id = $(this).data("taskId")
                            n_sorts.push(task_id) if task_id != undefined
                        )
                        
                        g_sorts = []
                        orderBy = $filter('orderBy')
                        soreted = orderBy($rootScope.tasks, 'sort')
                        soreted.forEach((task) ->
                            g_sorts.push(task.task_id) if task.complete_flag == false and task.processed == 0
                        )

                        lj = 0
                        for i in [0..o_sorts.length - 1]
                            for j in [lj..g_sorts.length - 1]
                                if o_sorts[i] == g_sorts[j]
                                    g_sorts[j] = n_sorts[i]
                                    lj = j + 1
                                    break
                        
                        sort = 0
                        for task_id in g_sorts
                            task = taskStorage.get_task(task_id)
                            if task != null
                                task.sort = sort
                                sort += 1
                        
                        $api.call('task/update_sorts', { sorts: g_sorts })
                            .then((res) ->
                                if res.data.err_code != 0
                                    logger.logError(res.data.err_msg)
                            )
                )
        }
)

.controller('taskCtrl', 
    ($scope, $api, taskStorage, missionStorage, filterFilter, HPRIV,
        $rootScope, $routeParams, logger, $session, $dateutil, $timeout, $dialogs) ->
        # Initialize
        $scope.sync = () ->
            $scope.mission_id = if $routeParams.mission_id != undefined then parseInt($routeParams.mission_id, 10) else null
            $rootScope.cur_mission = if $scope.mission_id != undefined then missionStorage.get_mission($scope.mission_id) else null

        $scope.sync()

        $scope.$on("synced-server", ->
            $scope.sync()

            $scope.refreshBackImage()
        )

        # Refresh list of tasks
        $scope.$on('refresh-tasks', (event) ->
        )

        # Search
        $scope.$on('search-task', (event) ->
            #$scope.refreshSearchFilter()
        )

        $scope.searchFilter = (task) ->
            if $rootScope.taskMode == 1
                # inbox mode
                if task.mission_id != null
                    return false
            else if $rootScope.taskMode == 2
                # mission mode
                if task.mission_id == null
                    return false

                if $rootScope.cur_mission 
                    if task.mission_id != $scope.mission_id
                        return false
                else
                    return false

            if $rootScope.task_search_string
                search_string = $rootScope.task_search_string.toLowerCase()
                if search_string != "" and task.task_name.toLowerCase().indexOf(search_string) == -1
                    return false

            return true
            
        $scope.searchFilter1 = (task) ->
            searched = $scope.searchFilter(task)
            if searched
                if task.complete_flag == false and task.processed == 0
                    return true

            return false
            
        $scope.searchFilter2 = (task) ->
            searched = $scope.searchFilter(task)
            if searched
                if task.complete_flag == false and task.processed == 1
                    return true

            return false
            
        $scope.searchFilter3 = (task) ->
            searched = $scope.searchFilter(task)
            if searched
                if task.complete_flag == true
                    return true

            return false
        
        # Refresh background
        $scope.refreshBackImage = ->
            cover = ''
            if $rootScope.taskMode == 2 && $rootScope.cur_mission && $rootScope.cur_mission.job_back_url != null
                if $rootScope.cur_mission.job_back_pos == 1
                    back_pos = " repeat"
                else if $rootScope.cur_mission.job_back_pos == 2
                    back_pos = " no-repeat center center"
                else if $rootScope.cur_mission.job_back_pos == 3
                    back_pos = " no-repeat left top"
                else
                    back_pos = " no-repeat center center"
                    cover = 'cover'
                angular.element('.page-tasks').removeClass('default-back')
                angular.element('.page-tasks').css('background', 'url(' + encodeURI($rootScope.cur_mission.job_back_url) + ') ' + back_pos)
            else
                angular.element('.page-tasks').addClass('default-back')
                angular.element('.page-tasks').css('background', '')

            angular.element('.page-tasks').css('-webkit-background-size', cover)
            angular.element('.page-tasks').css('-moz-background-size', cover)
            angular.element('.page-tasks').css('-o-background-size', cover)
            angular.element('.page-tasks').css('background-size', cover)
            return

        $scope.refreshBackImage()

        $scope.$on('refresh_back_image', ->
            $scope.refreshBackImage()
        )

        # Search tasks by mission
        $scope.$on('select-mission', (event) ->
            taskStorage.refresh_remaining()
            $scope.refreshBackImage()
        )

        # Search tasks by date
        $scope.$on('refresh-task-date', (event) ->
            taskStorage.search()
            $scope.refreshBackImage()
        )

        # Search tasks of this week
        $scope.$on('refresh-task-this-week', (event) ->
            taskStorage.search()
            $scope.refreshBackImage()
        )

        # Search completed
        $scope.loadCompleted = ->
            taskStorage.search_completed()

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
  
        # Title
        $scope.title = () ->
            title = ""
            if $rootScope.taskMode == 1
                # inbox mode
                title = "受信箱"
            else if $rootScope.taskMode == 2
                # mission mode
                if $rootScope.cur_mission
                    title = $rootScope.cur_mission.mission_name
                else
                    title = "チャットルームを選択してください"
            else if $rootScope.taskMode == 3
                # team mode
                if $rootScope.selectedMember
                    title = $rootScope.selectedMember.member_name
                else
                    title = "ユーザーを選択してください"
            else
                # priority mode
                title = "スター付き"

            return title

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