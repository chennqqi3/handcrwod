'use strict'

angular.module('app.sel_skill', [])

.directive('selSkill', 
    ($timeout, $session, $rootScope, HPRIV) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    element.on('click', () ->
                        can = element.attr('sel-skill')
                        if $rootScope.canEditTask() || can == "1"
                            panel = $('#panel-sel-skill')
                            if panel.is(":hidden")
                                $timeout( -> 
                                    $rootScope.$broadcast('show-panel', 'sel-skill', element.attr('ng-model'), ngModel.$viewValue)
                                , 10)
                            panel.toggle('slide', { direction: 'down' })
                    )
                    scope.$on('task-select-skill', (event, skills) ->
                        if skills
                            ngModel.$setViewValue(skills)
                        else
                            ngModel.$setViewValue(null)
                    )

                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.controller('selSkillCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger, $session, taskStorage) ->
        $scope.skills = []
        $scope.all_skills = []
        $scope.search_string = ''

        $scope.$on('show-panel', (event, panel_name, model_name, skills) ->
            if panel_name == 'sel-skill'
                $scope.skills = skills
                $scope.search()
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

        $scope.canAddSkill = ->
            can = true
            if $api.is_empty($scope.search_string)
                return false
            $scope.all_skills.forEach((skill) ->
                can = false if skill.skill_name == $scope.search_string
            )
            return can
        
        $scope.addSkill = ->
            $scope.all_skills.push( {selected: true, skill_name: $scope.search_string} )
            return

        $scope.clickOk = ->
            skills = []
            $scope.all_skills.forEach((skill) ->
                skills.push(skill.skill_name) if skill.selected
            )
            $scope.closePanel()
            $rootScope.$broadcast('task-select-skill', skills)
    
        $scope.search = ->
            if $rootScope.cur_home != null
                home_id = $rootScope.cur_home.home_id
            else
                home_id = null
            taskStorage.all_skills(home_id, (res) ->
                skills = []
                $scope.all_skills = []
                if res.err_code == 0
                    skills = res.skills
                skills.forEach((askill) ->
                    selected = false
                    $scope.skills.forEach((skill) ->
                        selected = true if askill == skill
                    )
                    $scope.all_skills.push({ selected: selected, skill_name: askill })
                )
            )
        
        $scope.closePanel = ->
            $('#panel-sel-skill').hide('slide', { direction: 'down' })
            return

        $scope.selectSkill = (skill) ->
            skill.selected = !skill.selected
)