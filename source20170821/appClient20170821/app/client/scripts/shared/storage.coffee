'use strict'

angular.module('app.storage', [])

.service('$syncServer', 
    ($rootScope, $timeout, AUTH_EVENTS, homeStorage, missionStorage, taskStorage, $session, $consts, $location) ->
        syncServer = this
        # Intialize global data
        init = ->
            $consts.init()

            # Task related data
            if $rootScope.tasks == undefined
                $rootScope.tasks = []
                $rootScope.priorityTasks = 0
                $rootScope.remainingITasks = 0
                $rootScope.remainingMTasks = 0
            if $rootScope.complete_offsets == undefined
                $rootScope.complete_offsets = {}
            if $rootScope.calendar_date == undefined
                $rootScope.calendar_date = null

            $rootScope.remainingSelTasks = 0

            # Home related data
            if $rootScope.homes == undefined
                $rootScope.homes = []
            if $rootScope.cur_home == undefined
                $rootScope.cur_home = null          
            $rootScope.cur_home_name = ->
                if $rootScope.cur_home != null
                    return $rootScope.cur_home.home_name  
                else
                    return "「グループなし」"

            # Mission related data
            if $rootScope.missions == undefined
                $rootScope.missions = []
            $rootScope.mission_complete_offset = 0

            if $rootScope.cur_mission == undefined
                $rootScope.cur_mission = null

            # Template related data
            if $rootScope.templates == undefined
                $rootScope.templates = []

            # alert related
            if $rootScope.alerts == undefined
                $rootScope.alerts = []

            return

        init()

        # Destroy global data
        $rootScope.$on(AUTH_EVENTS.logoutSuccess, ->
            # Task related data
            $rootScope.tasks = []
            $rootScope.priorityTasks = 0
            $rootScope.remainingITasks = 0
            $rootScope.remainingMTasks = 0
            $rootScope.complete_offsets = {}
            $rootScope.calendar_date = null
            $rootScope.remainingSelTasks = 0

            # Home related data
            $rootScope.homes = []
            $rootScope.cur_home = null

            # Mission related data
            $rootScope.missions = []
            $rootScope.cur_mission = null
            $rootScope.mission_complete_offset = 0

            # Template related data
            $rootScope.templates = []

            # alert related
            $rootScope.alerts = []
        )
        
        # Synchronize data with server
        syncServer.sync = (resync) ->
            if $session.user_id != null and $session.user_id != undefined
                homeStorage.search().then(->
                    if $rootScope.cur_home != null
                        missionStorage.search($rootScope.cur_home.home_id, $rootScope.cur_home.include_completed).then(->
                            $rootScope.$broadcast("synced-server")
                            ###
                            $timeout( ->
                                $('.sync-server').find('i').removeClass('glyphicon-spin')
                                if resync == true
                                    $timeout( ->
                                        syncServer.sync(true)
                                    , 120000)
                            )
                            ###
                        )
                    return
                )
            #else
            #    if resync == true
            #        $timeout( ->
            #            syncServer.sync(true)
            #        , 120000)
            return

        #syncServer.init = ->
        #    $timeout( ->
        #        syncServer.sync(true)
        #    , 120000)

        #syncServer.init()

        $rootScope.$on('refresh-homes', (event, msg) ->
            syncServer.sync()
        )

        $rootScope.$on('refresh-home-logo', (event, home_id) ->
            homeStorage.refresh_logo(home_id)
        )

        $rootScope.$on('refresh-missions', (event, new_mission_id) ->
            if $rootScope.cur_home != null
                missionStorage.search($rootScope.cur_home.home_id, $rootScope.cur_home.include_completed)
                    .then(->
                        if new_mission_id != undefined
                            $timeout(->
                                $location.path('/chats/' + new_mission_id)
                            )
                    )
        )
        
        $rootScope.$on('refresh-tasks', (event, msg) ->
            if $rootScope.cur_mission != null
                taskStorage.search($rootScope.cur_mission.mission_id)
        )

        $rootScope.$on('select-home', (event, new_mission_id) ->
            if $rootScope.cur_home != null
                missionStorage.search($rootScope.cur_home.home_id, $rootScope.cur_home.include_completed)
                #missionStorage.get_bot_messages($rootScope.cur_home.home_id)
        )

        $rootScope.$on('removed_home', () ->
            homeStorage.search().then((homes) ->
                if homes.length > 0
                    $location.path('/home/' + homes[0].home_id)
                else
                    $rootScope.cur_home = null
            )
        )

        return syncServer
)

.directive('syncServer', 
    ($timeout, $rootScope, $parse, $api, $session, $syncServer) ->
        return {
            restrict: 'A'
            link: (scope, element, attrs, ngModel) ->
                init = ->
                    $(element).click(->
                        $syncServer.sync()
                    )

                $timeout( -> 
                    init()
                , 10)
        }
)

.factory('templateStorage', 
    ($rootScope, $api, $session, $dateutil, filterFilter, AUTH_EVENTS) ->
        # Search templates
        search = ->
            $api.call("template/search")
                .then((res) ->
                    if res.data.err_code == 0
                        $rootScope.templates = res.data.templates
                        
                        return $rootScope.templates
                    else
                        return []
                 )

        return {
            search: search
        }
)

.factory('searchHistoryStorage', ->
    STORAGE_ID = 'search_history'
    EMPTY = '[]'

    return {
        get: ->
            try 
                return JSON.parse(localStorage.getItem(STORAGE_ID) || EMPTY )
            catch err
                return []

        put: (history)->
            localStorage.setItem(STORAGE_ID, JSON.stringify(history))
    }
)

