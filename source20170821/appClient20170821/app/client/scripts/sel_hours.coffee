'use strict'

angular.module('app.sel_hours', [])

.directive('selHours', 
    ($timeout, $session, $rootScope, HPRIV) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    element.on('click', () ->
                        if $rootScope.canEditTask()
                            panel = $('#panel-sel-hours')
                            if panel.is(":hidden")
                                $timeout( -> 
                                    $rootScope.$broadcast('show-panel', 'sel-hours', element.attr('ng-model'), ngModel.$viewValue)
                                , 10)
                            panel.toggle('slide', { direction: 'down' })
                    )
                    scope.$on('task-select-hours', (event, hours) ->
                        if hours
                            ngModel.$setViewValue(hours)
                        else
                            ngModel.$setViewValue(null)
                    )

                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.controller('selHoursCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger, $session, $timeout, $numutil) ->
        $scope.init = (hours) ->
            if hours == null
                $scope.hours =
                    day: 0
                    hour: 0
            else
                day = hours // 8
                hour = $numutil.to_decimal(hours - day * 8, 2)
                $scope.hours =
                    day: day
                    hour: hour

        $scope.init(null)

        $scope.$on('show-panel', (event, panel_name, model_name, hours) ->
            if panel_name == 'sel-hours'
                $scope.init(hours)
                $timeout(->
                    $('#txt_hours').focus()                  
                , 0 , false)
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

        $scope.clickOk = ->
            if $scope.form_sel_hours.$valid
                day = $numutil.to_num($scope.hours.day)
                hours = day * 8 + $numutil.to_num($scope.hours.hour)
                $scope.closePanel()
                $rootScope.$broadcast('task-select-hours', hours)

        $scope.canSubmit = ->
            return $scope.form_sel_hours.$valid
        
        $scope.closePanel = ->
            $('#panel-sel-hours').hide('slide', { direction: 'down' })
            return

        $scope.changeDay = ->
            day = $numutil.to_num($scope.hours.day)
            day = 0 if day < 0
            $timeout(->
                $scope.hours.day = day
            )

        $scope.changeHour = ->
            hour = $numutil.to_num($scope.hours.hour)
            hour = 7 if hour > 7 
            hour = 0 if hour < 0
            $timeout(->
                $scope.hours.hour = hour
            )

        return
)