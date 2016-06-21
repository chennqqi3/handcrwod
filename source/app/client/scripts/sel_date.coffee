'use strict'

angular.module('app.sel_date', [])

.directive('selDate', 
    ($timeout, $session, $rootScope, $parse, HPRIV) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    element.on('click', () ->
                        if $rootScope.canEditTask()
                            panel = $('#panel-sel-date')
                            start_date = if attrs.startDate then $parse(attrs.startDate)(scope) else null
                            end_date = if attrs.endDate then $parse(attrs.endDate)(scope) else null
                            if attrs.timeModel
                                timeValue = $parse(attrs.timeModel)(scope)
                            if panel.is(":hidden")
                                $timeout( -> 
                                    $rootScope.$broadcast('show-panel', 'sel-date', element.attr('ng-model'), ngModel.$modelValue, attrs.timeModel, timeValue, start_date, end_date)
                                , 10)
                                panel.show('slide', { direction: 'down' })
                            else 
                                $rootScope.$broadcast('hide-panel', 'sel-date', element.attr('ng-model'), ngModel.$modelValue)
                    )
                    scope.$on('set-date-to-control', (event, model_name, date, time_model_name, time) ->
                        if element.attr('ng-model') == model_name
                            ngModel.$setViewValue(date)
                            if element.attr('data-time-model') == time_model_name
                                setter = $parse(time_model_name).assign
                                if time == null
                                    setter(scope, null)
                                else
                                    dt = moment.tz(date, $session.time_zone)
                                    setter(scope, dt.format("YYYY-MM-DD") + " " + time)

                            $rootScope.$broadcast('change_' + model_name)
                        return
                    )

                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.controller('selDateCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger, $session, $dateutil) ->
        # Initialize calendar
        $scope.init = (model_name, date, time_model_name, time, start_date, end_date) ->
            $scope.hours = [0..23]
            $scope.minutes = [0, 15, 30, 45]
            $scope.model_name = model_name
            $scope.curdate = date
            
            $scope.time_model_name = time_model_name
            if time 
                $scope.timeMode = true
                dt = moment.tz(time, $session.time_zone)
                $scope.curtime = 
                    hour: dt.format('H')
                    minute: dt.format('m')
            else
                $scope.timeMode = false
                $scope.curtime = 
                    hour: 0
                    minute: 0

            start_date = start_date.substr(0, 10) if start_date != null && start_date != undefined
            end_date = end_date.substr(0, 10) if end_date != null && end_date != undefined
            date = date.substr(0, 10) if date != null && date != undefined
            $('#panel-sel-date .calendar').datepicker({format: "yyyy-mm-dd", language: "ja", startDate: start_date, endDate: end_date, todayHighlight: true})
                .datepicker('update', date)
            
        $scope.init('', null, '', null, null, null)

        # Changed date
        $('#panel-sel-date .calendar').on('changeDate', () -> 
            date = $(this).datepicker('getDate')
            if isNaN(date)
                date = null
            else
                date = $dateutil.std_date_string(date)
            $scope.curdate = date
        )

        # Clear date
        $scope.clearDate = ->
            $('#panel-sel-date .calendar').val("").datepicker('update', '')
            $rootScope.$broadcast('set-date-to-control', $scope.model_name, null, $scope.time_model_name, null)
            $scope.closePanel()
            return

        # Set date and close panel
        $scope.setDate = ->
            if $scope.curdate != null && $scope.timeMode 
                time = moment($scope.curtime.hour + ":" + $scope.curtime.minute, "H:m").format('HH:mm:00')
            else
                time = null
            $rootScope.$broadcast('set-date-to-control', $scope.model_name, $scope.curdate, $scope.time_model_name, time)
            $scope.closePanel() 

        # Close panel
        $scope.closePanel = ->
            $('#panel-sel-date').hide('slide', { direction: 'down' })
            $('#panel-sel-date .calendar').datepicker('remove')
            $scope.model_name = ''
            $scope.curdate = null
            return
                
        # Set time
        $scope.setTime = ->
            $scope.timeMode = true

        $scope.clearTime = ->
            $scope.timeMode = false

        # Global event
        $scope.$on('hide-panel', (event, panel_name, model_name, date, time_model_name, time, start_date, end_date) ->
            $scope.closePanel()
        )
                
        $scope.$on('show-panel', (event, panel_name, model_name, date, time_model_name, time, start_date, end_date) ->
            if panel_name == 'sel-date' and model_name != $scope.model_name 
                $scope.init(model_name, date, time_model_name, time, start_date, end_date)
            else
                $scope.closePanel()
        )

        $scope.$on('select-task', ->
            $scope.closePanel()
        )

        $scope.$on('select-mission', ->
            $scope.closePanel()
        )
)