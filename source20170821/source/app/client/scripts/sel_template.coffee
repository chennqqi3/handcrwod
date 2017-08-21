'use strict'

angular.module('app.sel_template', [])

.directive('selTemplate', 
    ($timeout, $session, $rootScope, logger) ->
        return {
            restrict: 'A'
            link: (scope, element, attrs) ->
                initPanel = ->
                    element.on('click', () ->
                        return if $rootScope.cur_mission == null
                        panel = $('#panel-sel-template')
                        if panel.is(":hidden")
                            $timeout( -> 
                                $rootScope.$broadcast('show-panel', 'sel-template')
                            , 10)
                        panel.toggle('slide', { direction: 'right' })
                    )
                    scope.$on('task-select-template', (event, template) ->
                    )

                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.controller('selTemplateCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger, $session, templateStorage, taskStorage, missionStorage, $dialogs) ->
        $scope.selected_template_id = null

        $scope.$on('show-panel', (event, panel_name) ->
            if panel_name != 'sel-template'
                $scope.closePanel()
            else
                templateStorage.search()
            return
        )
        
        # Hide panel
        $scope.closePanel = ->
            $('#panel-sel-template').hide('slide', { direction: 'right' })
            return

        # Select template
        $scope.selectTemplate = (template) ->
            if $scope.selected_template_id == template.template_id
                $scope.selected_template_id = null
            else
                $scope.selected_template_id = template.template_id

        # Remove template
        $scope.removeTemplate = (template) ->
            message = "このテンプレートを削除してもよろしいでしょうか？"
            $dialogs.confirm('テンプレート削除', message, '削除', "remove-template", template.template_id)
            return

        $scope.$on('remove-template', (event, template_id) ->
            $api.call("template/remove", {template_ids: template_id})
                .success((data, status, headers, config) ->
                    if data.err_code == 0
                        $rootScope.$broadcast('refresh-templates')     
                        logger.logSuccess('テンプレートが削除されました。')
                    else
                        logger.logError(data.err_msg)
                )
        )
        
        $scope.canImport = ->
            return $scope.selected_template_id != null

        # Process events
        $scope.$on('refresh-templates', (event) ->
            templateStorage.search()
        )

        $scope.$on('select-mission', ->
            $scope.closePanel()
            return
        )

        # Import template
        $scope.$on('import-template', ->
            params = 
                mission_id: $rootScope.cur_mission.mission_id
                template_id: $scope.selected_template_id
            $api.call("template/import", params)
                .success((data, status, headers, config) ->
                    if data.err_code == 0
                        # update mission
                        $rootScope.cur_mission.summary = data.summary
                        $rootScope.cur_mission.job_back = data.job_back
                        $rootScope.cur_mission.job_back_url = data.job_back_url
                        $rootScope.cur_mission.job_back_pos = data.job_back_pos
                        $rootScope.cur_mission.prc_back = data.prc_back
                        $rootScope.cur_mission.prc_back_url = data.prc_back_url
                        $rootScope.cur_mission.prc_back_pos = data.prc_back_pos

                        $rootScope.$broadcast('select-mission')
                        $rootScope.$broadcast('refresh-tasks')
                        missionStorage.search($rootScope.cur_home.home_id, $rootScope.cur_home.include_completed)
                        logger.logSuccess('プロセスが生成されました。')
                    else
                        logger.logError(data.err_msg)
                )
        )

        $scope.importConfirm = ->
            count = taskStorage.get_taskcount($rootScope.cur_mission.mission_id)
            if count > 0
                $dialogs.confirm('テンプレートから生成', '選択されたテンプレートからプロセスを生成します。\n【※注意：現在のチャットルームのすべてのタスクを上書きされます。】', '生成', "import-template")
            else
                $scope.$broadcast('import-template')
            
            return
)

.controller('templateAddCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger) ->
        $scope.initData = ->
            $scope.template = 
                template_name: ''
            $scope.form_template_add.$setPristine() if $scope.form_template_add

        $scope.initData()

        $scope.canSubmit = ->
            return $scope.form_template_add.$valid
        
        $scope.submitForm = ->
            $scope.template.mission_id = $rootScope.cur_mission.mission_id
            $api.call("template/add", $scope.template)
                .success((data, status, headers, config) ->
                    if data.err_code == 0
                        $rootScope.$broadcast('refresh-templates')     
                        logger.logSuccess('新しテンプレートが登録されました。')
                    else
                        logger.logError(data.err_msg)
                )
)