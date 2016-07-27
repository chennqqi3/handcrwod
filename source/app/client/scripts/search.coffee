'use strict'

angular.module('app.search', [])

.controller('searchCtrl', 
    ($scope, searchHistoryStorage, filterFilter, $rootScope, logger, $session) ->
        $scope.histories = searchHistoryStorage.get()

        if $scope.search_string == undefined
            $scope.search_string = ''

        if $rootScope.task_search_string == undefined
            $rootScope.task_search_string = ''

        $scope.refreshSearch = (search_string) ->
            if search_string != "" and $rootScope.task_search_string == search_string
                search_string = ""

            $rootScope.task_search_string = search_string
            $scope.search_string = search_string

            $rootScope.$broadcast('search-task')

        $scope.selectSearch = (search_string) ->
            $scope.refreshSearch(search_string)
            $('body').addClass("collapsed")
        
        $scope.saveHistory = (search_string) ->
            if search_string != "" and search_string != undefined
                history = 
                    search_string: $scope.search_string
                
                found = false
                $scope.histories.forEach((h) -> 
                    if h.search_string == history.search_string
                        found = true
                )

                if !found
                    $scope.histories.push(history)
                    searchHistoryStorage.put($scope.histories)
                    
                $('body').addClass("collapsed")
            return null

        $scope.canRemove = ->
            return $scope.histories.length > 0

        $scope.removeHistory = ->
            $scope.histories = []
            searchHistoryStorage.put($scope.histories)

)