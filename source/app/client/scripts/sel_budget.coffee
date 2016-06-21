'use strict'

angular.module('app.sel_budget', [])

.directive('selBudget', 
    ($timeout, $session, $rootScope, HPRIV) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    element.on('click', () ->
                        if $rootScope.canEditTask()
                            panel = $('#panel-sel-budget')
                            if panel.is(":hidden")
                                $timeout( -> 
                                    $rootScope.$broadcast('show-panel', 'sel-budget', element.attr('ng-model'), ngModel.$viewValue)
                                , 10)
                            panel.toggle('slide', { direction: 'down' })
                    )
                    scope.$on('task-select-budget', (event, budget) ->
                        if budget
                            ngModel.$setViewValue(budget)
                        else
                            ngModel.$setViewValue(null)
                    )

                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.controller('selBudgetCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger, $session, $timeout, $numutil) ->
        $scope.budget = ""

        $scope.$on('show-panel', (event, panel_name, model_name, budget) ->
            if panel_name == 'sel-budget'
                $scope.budget = budget
                $timeout(->
                    $('#txt_budget').focus()                  
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
            $scope.closePanel()
            budget = $numutil.to_num($scope.budget)
            $rootScope.$broadcast('task-select-budget', budget)

        $scope.canSubmit = ->
            return $scope.form_sel_budget.$valid
        
        $scope.closePanel = ->
            $('#panel-sel-budget').hide('slide', { direction: 'down' })
            return

        $scope.changeBudget = ->
            budget = $numutil.to_num($scope.budget)
            budget = 0 if budget < 0
            $timeout(->
                $scope.budget = budget
            )
)