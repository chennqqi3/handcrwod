'use strict'

angular.module('app.sel_performer', [])

.directive('selPerformer', 
    ($timeout, $session, $rootScope, $parse, HPRIV) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    element.on('click', () ->
                        if $rootScope.canEditTask()
                            panel = $('#panel-sel-performer')
                            if attrs.taskId
                                task_id = $parse(attrs.taskId)(scope) 
                            mission_id = $parse(attrs.missionId)(scope)
                            if mission_id != null
                                if panel.is(":hidden")
                                    $timeout( -> 
                                        $rootScope.$broadcast('show-panel', 'sel-performer', element.attr('ng-model'), ngModel.$viewValue, task_id)
                                    , 10)
                                panel.toggle('slide', { direction: 'down' })
                    )
                    scope.$on('task-select-performer', (event, user) ->
                        if user
                            ngModel.$setViewValue(user.user_id)
                            $parse(attrs.performerName).assign(scope, user.user_name)
                            $parse(attrs.avartar).assign(scope, user.avartar)
                        else
                            ngModel.$setViewValue(null)

                        return
                    )

                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.controller('selPerformerCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger, $session, $dialogs, taskStorage) ->
        $scope.performer_id = ''
        $scope.users = []
        $scope.level_readonly = true

        $scope.$on('show-panel', (event, panel_name, model_name, performer_id, task_id) ->
            if panel_name == 'sel-performer'
                $scope.performer_id = performer_id
                $scope.search(task_id)
            else
                $scope.closePanel()
            return
        )

        $scope.$on('select-task', ->
            $scope.closePanel()
        )

        $scope.$on('select-mission', ->
            $scope.closePanel()
        )
    
        $scope.search = (task_id) ->
            taskStorage.get_candidates(task_id, (res) ->
                if res.err_code == 0
                    $scope.users = res.users
                else
                    logger.logError(res.err_msg)
                return
            )
            return
        
        $scope.closePanel = ->
            $('#panel-sel-performer').hide('slide', { direction: 'down' })
            return

        $scope.selectPerformer = (user) ->
            if user.mission_member == false
                message = "チャットルーム共有されないユーザーにタスクを割り当てるようにしています。よろしいでしょうか？"
                $dialogs.confirm('割り当て確認', message, 'はい', "confirm-perform", user)
            else 
                $rootScope.$broadcast('task-select-performer', user);
                $scope.closePanel()
            return

        $scope.$on('confirm-perform', (event, user) ->
            $rootScope.$broadcast('task-select-performer', user);
            $scope.closePanel()
            return
        )
)