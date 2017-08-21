'use strict'

angular.module('app.task.edit', [])

# cusor focus when dblclick to edit
.directive('taskFocus', 
    ($timeout) ->
        return {
            link: (scope, ele, attrs) ->
                scope.$watch(attrs.taskFocus, (newVal) ->
                    if newVal
                        $timeout( ->
                            ele[0].focus()
                        , 0, false)
                )
        }
)

.directive('taskSummaryFocus', 
    ($timeout) ->
        return {
            link: (scope, ele, attrs) ->
                scope.$watch(attrs.taskSummaryFocus, (newVal) ->
                    if newVal
                        $timeout( ->
                            ele[0].focus()
                        , 0, false)
                )
        }
)

.controller('taskeditCtrl', 
    ($scope, $api, taskStorage, filterFilter, $rootScope, HPRIV,
        logger, $session, $dateutil, CONFIG, $dialogs) ->
        $scope.task = null
        # Initialize
        $scope.init = (task) ->
            $scope.activeTab = 0
            $scope.max_level = 5
            $scope.level_readonly = !$rootScope.canEditTask()
            $scope.print_url = CONFIG.API_BASE + "task/print_task?TOKEN=" + $session.getTOKEN() + "&task_id="
            $scope.editTaskNameMode = false
            $scope.editSummaryMode = false

            if task != null
                if task.progress == null
                    task.progress = 0
                taskStorage.get_skills(task.task_id, (res) ->
                    if res.err_code == 0
                        $scope.task.skills = res.skills
                    else
                        $scope.task.skills = []
                )
                $scope.get_comments(task)
                task.org_task_name = task.task_name
                $scope.task = task

        $scope.init(null)

        # Show or close task
        $scope.$on('select-task', (event, task) ->
            task = null if task == undefined
            if task == null
                $('#panel-task-edit').hide('slide' , {direction: 'right'})
            else 
                $('#panel-task-edit').show('slide' , {direction: 'right'})
                $scope.init(task)
        )

        $scope.$on('reload_session', ->
            $scope.init(null)
        )

        $scope.$on('select-mission', ->
            $scope.closePanel()
        )

        $scope.$on('refresh-task-date', ->
            $scope.closePanel()
        )

        $scope.$on('refresh-task-this-week', ->
            $scope.closePanel()
        )

        $scope.$on('select-member', ->
            $scope.closePanel()
        )

        $scope.$on('search-task', ->
            $scope.closePanel()
        )

        $scope.$on('refreshed-tasks', (event) ->
            if $rootScope.tasks.length == 0
                $scope.closePanel()

            found = false
            if $rootScope.tasks.length > 0 && $scope.task != null
                for i in [0..$rootScope.tasks.length - 1]
                    if $scope.task.task_id == $rootScope.tasks[i].task_id
                        $scope.task = $rootScope.tasks[i]
                        found = true

            if found == false
                $scope.closePanel()
        )

        # Close panel
        $scope.closePanel = ->
            $rootScope.$broadcast('select-task', null)
            return

        # Edit summary
        $scope.editSummary = () ->
            if $rootScope.canEditTask()
                $scope.editSummaryMode = true
            return

        $scope.exitEditSummary = () ->
            $scope.editSummaryMode = false
            return

        $scope.submitSaveSummary = (task) ->
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, summary: task.summary })
            $scope.editSummaryMode = false
            return

        # Edit task name
        $scope.changeTaskName = (task) ->
            task.task_name = task.task_name.trim()
            if task.task_name == task.org_task_name
                $scope.editTaskNameMode = false
                return
            if task.task_name == ""
                task.task_name = task.org_task_name
            else
                task.org_task_name = task.task_name
                taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, task_name: task.task_name }, (res) ->
                    if res.err_code != 0
                        logger.logError(res.err_msg)
                )
            $scope.editTaskNameMode = false

        $scope.editTaskName = (task) ->
            $scope.editTaskNameMode = true

        # Change priority
        $scope.checkPriority = (task) ->
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, priority: task.priority }, (res) ->
                if res.err_code != 0
                    logger.logError(res.err_msg)
            )
            taskStorage.refresh_remaining()

        # Change mission
        $scope.changeMission = (task) ->
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id }, (res) ->
                if res.err_code == 0
                    $rootScope.$broadcast('refresh-tasks')
                else
                    logger.logError(res.err_msg)
            )
            taskStorage.refresh_remaining()
            return

        # Change skill
        $scope.changeSkills = (task) ->
            skills = task.skills.join ',' 
            taskStorage.set_skills(task.task_id, skills, (res) ->
                if res.err_code != 0
                    logger.logError(res.err_msg)
            )
            return

        # Change performer
        $scope.changePerformer = (task) ->
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, performer_id: task.performer_id }, (res) ->
                if res.err_code != 0
                    logger.logError(res.err_msg)
            )
            #$scope.recalcBudget(task)
            return

        # Change level
        $scope.changeLevel = (task) ->
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, level: task.level }, (res) ->
                if res.err_code != 0
                    logger.logError(res.err_msg)
            )
            return

        # Change date
        $scope.$on('change_task.plan_start_date', ->
            $scope.changeDate($scope.task)
        )
        $scope.$on('change_task.plan_end_date', ->
            $scope.changeDate($scope.task)
        )
        $scope.changeDate = (task) ->
            if task.plan_start_date != null and task.plan_end_date != null
                sd = moment(task.plan_start_date)
                ed = moment(task.plan_end_date)
                if task.plan_start_date == task.plan_end_date and task.plan_start_time != null and task.plan_end_time != null
                    sd = moment(task.plan_start_time)
                    ed = moment(task.plan_end_time)
                hours = (ed.diff(sd, 'hours') + 1)
                if hours > 7
                    task.plan_hours = (ed.diff(sd, 'days') + 1) * 8
                else if hours <= 0
                    task.plan_hours = null
                else
                    task.plan_hours = hours
                #$scope.recalcBudget(task)

            param = 
                task_id: task.task_id
                mission_id: task.mission_id
                plan_start_date: task.plan_start_date
                plan_start_time: task.plan_start_time
                plan_end_date: task.plan_end_date
                plan_end_time: task.plan_end_time
                plan_hours: task.plan_hours
            taskStorage.edit(param, (res) ->
                if res.err_code == 0
                    $rootScope.$broadcast('refresh-tasks')
                else
                    logger.logError(res.err_msg)
            )
            return

        # Change plan hours
        $scope.changePlanHours = (task) ->
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, plan_hours: task.plan_hours }, (res) ->
                if res.err_code != 0
                    logger.logError(res.err_msg)
                else
                    $rootScope.cur_mission.total_budget = res.total_budget
                    $rootScope.cur_mission.total_hours = res.total_hours
                    $rootScope.$broadcast('refresh-critical')
            )
            #$scope.recalcBudget(task)
            return

        # Change budget
        $scope.changeBudget = (task) ->
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, plan_budget: task.plan_budget }, (res) ->
                if res.err_code == 0
                    $rootScope.cur_mission.total_budget = res.data.total_budget
                    $rootScope.cur_mission.total_hours = res.data.total_hours               
            )
            return

        $scope.recalcBudget = (task) ->
            if task.perfomer_id != null and task.performer_id != $session.user_id
                $api.call("user/get_daily_amount", { user_id: task.performer_id })
                    .then((res)->
                        if res.data.err_code == 0
                            task.plan_budget = task.plan_hours * res.data.daily_amount
                            $scope.changeBudget(task)
                    )
            else
                task.plan_budget = 0
                $scope.changeBudget(task)

        # Refresh comments
        $scope.$on('refresh-comments', (event, new_comment) ->
            $scope.get_comments($scope.task)
            return
        )

        $scope.get_comments = (task) ->
            taskStorage.get_comments(task.task_id, (res) ->
                if res.err_code == 0
                    task.comments = res.comments
                else
                    task.comments = []
                return
            )

        # Add comment
        $scope.canAddComment = ->
            return $scope.form_comment_add.$valid
        
        $scope.submitAddComment = (task) ->
            taskStorage.add_comment(task.task_id, $scope.comment, (res) ->
                if res.err_code == 0
                    $rootScope.$broadcast('refresh-comments', res.comment)
                    $scope.comment = ""
                    $scope.form_comment_add.$setPristine()
                else
                    logger.logError(res.err_msg)
            )
            return

        # Remove comment
        $scope.removeComment = (task_comment_id) ->
            message = "コメントを削除してもよろしいでしょうか？"
            $dialogs.confirm('コメント削除', message, '削除', "remove-comment", task_comment_id)
            return

        $scope.$on('remove-comment', (event, task_comment_id) ->
            taskStorage.remove_comment(task_comment_id, (res) ->
                if res.err_code == 0
                    $rootScope.$broadcast('refresh-comments')
                else
                    logger.logError(res.err_msg)
            )
            return
        )

        # Remove task
        $scope.removeTask = (task) ->
            message = "タスクを削除してもよろしいでしょうか？"
            $dialogs.confirm('タスク削除', message, '削除', "remove-task", task)
            return

        $scope.$on('remove-task', (event, task) ->
            taskStorage.remove(task, (res) ->
                if res.err_code == 0
                    $rootScope.$broadcast('refresh-tasks')
                    logger.logSuccess('タスクが削除されました。')
                else
                    logger.logError(res.err_msg)
            )
            $scope.closePanel()
            return
        )

        # Request entrance
        $scope.reqEntrance = (task) ->
            if task.plan_end_date == null
                logger.logError('期限を設定していないと外部依頼ができません。')
            else
                $dialogs.reqEntrance(task)
            return

        # Help entrance
        $scope.helpEntrance = (task) ->
            $dialogs.helpEntrance(task)
            return

        # Upload attach for mission
        $scope.onUploadAttach = () ->
            $dialogs.uploadAttach(1, $scope.task.task_id)

        $scope.canProgress = () ->
            return $scope.task != null && ($rootScope.canEditTask() || $scope.task.performer_id == $session.user_id)

        $scope.changeProgress = (task) ->
            taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, progress: task.progress }, (res) ->
                if res.err_code == 0
                    task.complete_flag = res.complete_flag == 1
                    $rootScope.$broadcast('refresh-progress', task)
                else
                    logger.logError(res.err_msg)
            )
)