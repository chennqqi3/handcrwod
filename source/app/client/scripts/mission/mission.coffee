'use strict'

angular.module('app.mission', [])

.directive('missionSortable', 
    ($rootScope, missionStorage, $filter, $api, logger) ->
        o_sorts = []
        return {
            restrict: 'A'
            link: (scope, ele, attrs) ->
                $(ele).sortable( 
                    distance: 30
                    axis: 'y'
                    handle: '.mission-name'
                    start: (event, ui) ->
                        o_sorts = []
                        $('.mission-sort .mission-item').each( ->
                            mission_id = $(this).data("missionId")
                            o_sorts.push(mission_id) if mission_id != undefined
                        )
                    update: (event, ui) ->
                        n_sorts = []
                        $('.mission-sort .mission-item').each( ->
                            mission_id = $(this).data("missionId")
                            n_sorts.push(mission_id) if mission_id != undefined
                        )
                        
                        g_sorts = []
                        orderBy = $filter('orderBy')
                        soreted = orderBy($rootScope.missions, 'sort')
                        soreted.forEach((mission) ->
                            g_sorts.push(mission.mission_id) if mission.complete_flag == false
                        )

                        lj = 0
                        for i in [0..o_sorts.length - 1]
                            for j in [lj..g_sorts.length - 1]
                                if o_sorts[i] == g_sorts[j]
                                    g_sorts[j] = n_sorts[i]
                                    lj = j + 1
                                    break
                        
                        sort = 0
                        for mission_id in g_sorts
                            mission = missionStorage.get_mission(mission_id)
                            if mission != null
                                mission.sort = sort
                                sort += 1
                        
                        $api.call('mission/update_sorts', { sorts: g_sorts })
                            .then((res) ->
                                if res.data.err_code != 0
                                    logger.logError(res.data.err_msg)
                            )
                )
        }
)

.controller('missionCtrl', 
    ($scope, $api, filterFilter, $rootScope, logger, missionStorage, $session, $timeout, $dialogs, $location) ->
        # Initialize
        $scope.init = () ->
            #missionStorage.init()

        $scope.init()
        
        # Refresh list of missions
        $scope.$on('refresh-missions', (event, new_mission_id) ->
            missionStorage.search()
                .then((missions) ->
                    missions.forEach((mission) ->
                        if new_mission_id != undefined && mission.mission_id == new_mission_id
                            $scope.selectMission(mission)
                    )
                )
        )

        # Search
        $scope.searchFilter = (mission) ->
            if $scope.search_string
                search_string = $scope.search_string.toLowerCase()
                if search_string != "" and mission.mission_name.toLowerCase().indexOf(search_string) == -1
                    return false

            return true
            
        $scope.searchFilter1 = (mission) ->
            searched = $scope.searchFilter(mission)
            if searched
                if mission.complete_flag == false
                    return true

            return false
            
        $scope.searchFilter2 = (mission) ->
            searched = $scope.searchFilter(mission)
            if searched
                if mission.complete_flag == true
                    return true

            return false

        # Search completed
        $scope.loadCompleted = ->
            missionStorage.search_completed()

        # Show mission add panel
        $scope.showMissionAdd = ->
            panel = $('.panel-mission')
            if !panel.hasClass('show-add-bar')
                panel.addClass('show-add-bar')
            return

        # Select mission
        $scope.selectMission = (mission) ->
            path = "/" + $rootScope.getMenu($location.path())
            if $rootScope.cur_mission == mission
                $rootScope.cur_mission = null
            else
                $rootScope.cur_mission = mission
                path = path + '/' + mission.mission_id

            $rootScope.tasks.forEach((task) ->
                task.sort0 = task.sort if task.complete_flag == false and task.processed == 0
            )
            #$rootScope.$broadcast('select-mission')
            $location.path(path)
            $('body').addClass("collapsed")
            return

        # Edit mission
        $scope.editMission = ->
            panel = $('#panel-edit-mission')
            if panel.is(":hidden")
                $timeout( -> 
                    $rootScope.$broadcast('edit-mission', 'share', $rootScope.cur_mission)
                , 10)
            panel.toggle('slide', { direction: 'right' })
            return

        # Import csv
        $scope.importCSV = (files)->
            if files.length == 0
                return
            file = files[0]

            message = "このCSVファイルを取り込みます。よろしいでしょうか？"
            $dialogs.confirm('CSVインポート', message, '確認', "import-csv", file)
            return

        $scope.$on('import-csv', (event, file) ->
            $api.import_csv(file).progress( (evt) ->
                #console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total))
            ).success( (data, status, headers, config) ->
                if data.err_code == 0
                    logger.logSuccess(data.imported + "件のタスクが登録されました。")

                    $rootScope.$broadcast('refresh-missions')
                    $rootScope.$broadcast('refresh-tasks')
                else
                    logger.logError(data.err_msg)

                angular.element("input[type='file']").val('')
            )
            return
        )
        
        return
)