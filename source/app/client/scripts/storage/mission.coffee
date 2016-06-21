'use strict'

angular.module('app.storage.mission', [])

.factory('missionStorage', 
    ($rootScope, $api, $session, $dateutil, filterFilter, AUTH_EVENTS, $auth, $chat) ->
        # Initialize
        init = ->
            if $auth.isAuthenticated()
                search()
        
        # Search missions
        search = (home_id, include_completed) ->
            params = 
                home_id: home_id
                include_completed: include_completed

            $api.call("mission/search", params)
                .then((res) ->
                    if res.data.err_code == 0
                        $rootScope.missions = reset_order(res.data.missions)
                        $rootScope.mission_complete_offset = 0
                    else
                        $rootScope.missions = []

                    $rootScope.$broadcast('refreshed-missions')

                    return $rootScope.missions
                )

        reset_order = (missions) ->
            order = 0
            missions.forEach((mission) ->
                mission.order = order
                order += 1

                mission.complete_flag = mission.complete_flag == 1
                if mission.private_flag == 3
                    $rootScope.bot_mission = mission
            )
            return missions
            
        # Get unpinned missions
        unpinned_missions = (home_id, private_flag, callback) ->
            $params = 
                home_id: home_id
                private_flag: private_flag

            $api.call("mission/unpinned_missions", $params)
                .then((res) ->
                    if res.data.err_code == 0
                        missions = res.data.missions
                        missions.forEach((mission) ->
                            mission.complete_flag = mission.complete_flag == 1
                        )

                    if callback != undefined
                        callback(res.data)
                 )

        # Search completed missions
        search_completed = () ->
            params = 
                offset: $rootScope.mission_complete_offset
                complete_flag: 1
                limit: 10

            $api.call("mission/search", params)
                .then((res) ->
                    if res.data.err_code == 0
                        missions = res.data.missions
                        missions.forEach((mission) ->
                            mission.complete_flag = mission.complete_flag == 1
                            $rootScope.missions.push(mission)
                        )
                        refresh_remaining()

                        $rootScope.mission_complete_offset = params.offset + missions.length
                        return $rootScope.missions
                    else
                        return []
                )

        # Refresh remaining tasks 
        refresh_remaining = ->
            sel_mission_id = $rootScope.cur_mission.mission_id if $rootScope.cur_mission != null
            if $rootScope.missions != undefined
                $rootScope.missions.forEach((mission) ->
                    if $rootScope.tasks != undefined
                        remaining = 0
                        $rootScope.tasks.forEach((task) ->
                            if task.complete_flag == false and task.performer_id == $session.user_id and task.mission_id == mission.mission_id
                                remaining += 1
                        )
                        mission.remainingTasks = remaining
                )

        # Refresh sort number
        refresh_sort = ->
            sort = 0
            $rootScope.missions.forEach((mission) ->
                if mission.complete_flag == false
                    mission.sort0 = sort
                    mission.sort = sort
                    sort += 1
            )

        # Get mission from mission_id
        get_mission = (mission_id) ->
            for mission in $rootScope.missions
                if mission.mission_id == mission_id
                    return mission
            return null
        set_mission = (mission) ->
            if $rootScope.missions != null && $rootScope.missions.length > 0
                for i in [0..$rootScope.missions.length-1]
                    if $rootScope.missions[i].mission_id == mission.mission_id
                        $rootScope.missions[i] = mission

                        $rootScope.missions = reset_order($rootScope.missions)
                        return

            $rootScope.missions.push(mission)
            $rootScope.missions = reset_order($rootScope.missions)
            return null

        add = (mission, callback) ->
            $api.call("mission/add", mission)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.mission('add', res.data.mission_id, res.data.home_id)
                )

        get = (mission_id, callback) ->
            params = 
                mission_id: mission_id

            $api.call("mission/get", params)
                .then((res) ->
                    if res.data.err_code == 0
                        res.data.mission.complete_flag = res.data.mission.complete_flag == 1
                    if callback != undefined
                        callback(res.data)
                )

        edit = (mission, callback) ->
            $api.call("mission/edit", mission)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.mission('edit', res.data.mission_id, res.data.home_id)
                )

        open = (mission_id, callback) ->
            params = 
                mission_id: mission_id

            $api.call("mission/open", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        pin = (mission_id, pinned, callback) ->
            params = 
                mission_id: mission_id
                pinned: pinned

            $api.call("mission/pin", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        attaches = (mission_id, callback) ->
            params = 
                mission_id: mission_id

            $api.call("mission/attaches", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        remove = (mission, callback) ->
            params = 
                mission_id: mission.mission_id

            $api.call("mission/remove", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.mission('remove', res.data.mission_id, res.data.home_id)
                )

        # member related
        open_member = (home_id, user_id, callback) ->
            params = 
                home_id: home_id
                user_id: user_id

            $api.call("mission/open", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.home('open_member', home_id)
                )

        remove_member = (mission_id, user_id, callback) ->
            params = 
                mission_id: mission_id
                user_id: user_id

            $api.call("mission/remove_member", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.mission('remove_member', res.data.mission_id, res.data.home_id)
                )

        invitable_members = (mission_id, callback) ->
            params = 
                mission_id: mission_id
            $api.call("mission/invitable_members", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        invite = (req, callback) ->
            $api.call("mission/invite", req)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.mission('invite', res.data.mission_id, res.data.home_id)
                )

        remove_attach = (mission_id, mission_attach_id, callback) ->
            params = 
                mission_id: mission_id
                mission_attach_id: mission_attach_id

            $api.call("mission/delete_attach", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        complete = (mission_id, complete_flag, callback) ->
            params = 
                mission_ids: mission_id
                complete_flag: complete_flag

            $api.call("mission/complete", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        break_mission = (mission_id, complete_flag, callback) ->
            params = 
                mission_id: mission_id

            $api.call("mission/break_mission", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        set_repeat = (mission_id, repeat_type, repeat_weekday, repeat_month, repeat_monthday, callback) ->            
            if repeat_weekday == undefined || repeat_weekday < 0 || repeat_weekday > 6
                repeat_weekday = 1
            if repeat_month == undefined || repeat_month < 1 || repeat_month > 12
                repeat_month = 1
            if repeat_monthday == undefined || repeat_monthday < 1 || repeat_monthday > 31
                repeat_monthday = 1

            params = 
                mission_id: mission_id
                repeat_type: repeat_type
                repeat_weekday: repeat_weekday
                repeat_month: repeat_month
                repeat_monthday: repeat_monthday

            $api.call("mission/set_repeat", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        set_back_pos = (mission_id, type, back_pos, callback) ->
            params = 
                mission_id: mission_id
                type: type
                back_pos: back_pos

            $api.call("mission/set_back_pos", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        upload_back_image = (mission_id, type, file) ->
            $api.upload_file('mission/upload_back_image', file, {
                mission_id: mission_id
                type: type
            })

        delete_back_image = (mission_id, type, callback) ->
            params = 
                mission_id: mission_id
                type: type

            $api.call("mission/delete_back_image", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        get_bot_messages = (home_id) ->
            $chat.bot_message(home_id)
            return

        return {
            init: init
            search: search
            unpinned_missions: unpinned_missions
            search_completed: search_completed
            refresh_remaining: refresh_remaining
            get_mission: get_mission
            set_mission: set_mission
            add: add
            get: get
            edit: edit
            open: open
            pin: pin
            attaches: attaches
            remove: remove

            open_member: open_member
            remove_member: remove_member
            invitable_members: invitable_members
            invite: invite

            remove_attach: remove_attach
            complete: complete
            break_mission: break_mission
            set_repeat: set_repeat

            set_back_pos: set_back_pos
            upload_back_image: upload_back_image
            delete_back_image: delete_back_image

            get_bot_messages: get_bot_messages
        }
)
