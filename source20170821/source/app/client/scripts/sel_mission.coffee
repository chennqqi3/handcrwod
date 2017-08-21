'use strict'

angular.module('app.sel_mission', [])

.directive('selMission', 
    ($timeout, $session, $rootScope, logger) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    element.on('click', () ->
                        panel = $('#panel-sel-mission')
                        if scope.task.processed == 1
                            panel.hide('slide', { direction: 'down' })
                            logger.logWarning("プロセス化されたタスクはチャットルームを選択することができません。")
                        else
                            if panel.is(":hidden")
                                $timeout( -> 
                                    $rootScope.$broadcast('show-panel', 'sel-mission', element.attr('ng-model'), ngModel.$viewValue)
                                , 10)
                            panel.toggle('slide', { direction: 'down' })
                    )
                    scope.$on('task-select-mission', (event, mission) ->
                        if mission
                            ngModel.$setViewValue(mission.mission_id) # task.mission_id
                            scope.task.mission_name = mission.mission_name
                        else
                            ngModel.$setViewValue(null)
                            scope.task.mission_name = null
                    )

                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.controller('selMissionCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger, $session, missionStorage) ->
        $scope.selected_mission_id = ''

        $scope.$on('show-panel', (event, panel_name, model_name, mission_id) ->
            if panel_name == 'sel-mission'
                $scope.selected_mission_id = mission_id
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
        
        $scope.closePanel = ->
            $('#panel-sel-mission').hide('slide', { direction: 'down' })
            return

        $scope.selectMission = (mission) ->
            mission_id = if mission == null then null else mission.mission_id
            $scope.selected_mission_id = mission_id
            $scope.closePanel()
            $rootScope.$broadcast('task-select-mission', mission)
                
)