'use strict'

angular.module('app.storage.mission', [])

.factory('missionStorage', 
    ($rootScope, $api, $session, $dateutil, $chat, CONFIG) ->
        # Search missions
        search = (home_id, include_completed) ->
            params = 
                home_id: home_id
                include_completed: include_completed

            $api.call("mission/search", params)
                .then((res) ->
                    if res.data.err_code == 0
                        $rootScope.missions = reset_order(res.data.missions)
                        $rootScope.missions.sort((a, b)->
                            return a.order - b.order
                        )
                        $rootScope.mission_complete_offset = 0
                    else
                        $rootScope.missions = []

                    $rootScope.$broadcast('mission-search-post')

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
            if $rootScope.missions
                for mission in $rootScope.missions
                    if mission.mission_id == mission_id
                        return mission
            return null

        set_mission = (mission) ->
            if $rootScope.missions != null && $rootScope.missions.length > 0
                for i in [0..$rootScope.missions.length-1]
                    if $rootScope.missions[i].mission_id == mission.mission_id
                        if $rootScope.missions[i] != mission
                            $rootScope.missions[i] = mission
                            $rootScope.missions = reset_order($rootScope.missions)
                        return

            $rootScope.missions.push(mission)
            $rootScope.missions = reset_order($rootScope.missions)
            return null

        set_cur_mission = (mission, toStorage) ->
            if toStorage == undefined
                toStorage = true
            $rootScope.cur_mission = mission
            select_mission_in_nav()
            if $rootScope.cur_mission
                $rootScope.cur_mission.visible = true
                set_mission($rootScope.cur_mission)
            if toStorage
                $session.statesToStorage()
            return

        add = (mission, callback) ->
            $api.call("mission/add", mission)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.mission('add', res.data.mission_id, res.data.home_id)
                )

        get_name = (mission_id, callback) ->
            params =
                mission_id: mission_id

            $api.call("mission/get_name", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        get = (mission_id, callback) ->
            params = 
                mission_id: mission_id

            $api.call("mission/get", params)
                .then((res) ->
                    if res.data.err_code == 0
                        res.data.mission.complete_flag = res.data.mission.complete_flag == 1
                        for icon in res.data.mission.emoticons
                            $api.init_emoticon(icon)

                        $rootScope.emoticons = res.data.mission.emoticons

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

        self_invite = (mission_id, invite_key, callback) ->
            req = 
                mission_id: mission_id
                invite_key: invite_key
            $api.call("mission/self_invite", req)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
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

        break_mission = (mission_id, callback) ->
            params = 
                mission_id: mission_id

            $api.call("mission/break_mission", params)
                .then((res) ->
                    if res.data.err_code == 0
                        if $rootScope.missions
                            for i in [0..$rootScope.missions.length - 1]
                                if $rootScope.missions[i].mission_id == mission_id
                                    $rootScope.missions.splice(i, 1)
                                    break
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

        mission_html_id = (mission) ->
            if (mission.private_flag == 0 || mission.private_flag == 1) 
                return 'mission_' + mission.mission_id
            else if (mission.private_flag == 2)
                return 'mission2_' + mission.user_id

        select_mission_in_nav = () ->
            if $rootScope.nav_id
                $('.nav-bar>li').removeClass('active')
                $('.nav-bar .list-item').removeClass('active')
                if $rootScope.nav_id.indexOf('chatroom_') == 0
                    if $rootScope.cur_mission
                        id = mission_html_id($rootScope.cur_mission)
                        $('#' + id).addClass('active').removeClass('hide')
                else if $rootScope.nav_id == 'home'
                    $('.nav-bar .nav-home').addClass('active')
                else if $rootScope.nav_id == 'settings'
                    $('.nav-bar .my-avartar').addClass('active')
            return

        mission_unreads_to_html = (mission) ->
            txt = ''
            if (mission.unreads > 0)
                txt += '<i class="badge badge-danger">' + mission.unreads + '</i> '

            if (mission.to_unreads > 0)
                txt += '<i class="badge badge-success"><small>TO</small>' + mission.to_unreads + '</i> '

            return txt

        mission_to_html = (mission, groups, include_self) ->
            html = ""
            if (include_self == undefined)
                include_self = true

            mission_id = mission.mission_id
            if (mission.private_flag == 0 || mission.private_flag == 1)
                ###
                <li data-ng-repeat="mission in missions0 = (missions | filter:roomFilter | filter:{mission_name:search_string} | orderBy:'order') track by mission.mission_id" class="list-item" ng-class="{'active': nav_id=='chatroom_' + mission.mission_id }" data-mission-id="{{mission.mission_id}}" ng-show="search_string !='' && search_string != null || groups[0] || mission.visible">
                    <div class="info">
                        <i class="badge badge-danger" ng-show="mission.unreads > 0">{{mission.unreads}}</i>
                        <a href="javascript:;" ng-class="{'btn-pin': mission.pinned!=1}" ng-click="pinMission(mission)"><i class="icon-pin"></i></a>
                    </div>
                    <a ng-href="#/chats/{{mission.mission_id}}" title="{{::mission.mission_name}}"><i class="fa fa-lock" ng-if="mission.private_flag==1"></i> {{mission.mission_name}}</a>
                </li>
                ###

                item_class = ' '
                if ($rootScope.nav_id == 'chatroom_' + mission.mission_id)
                    item_class += 'active ';
                if (!(groups[0] || mission.visible))
                    item_class += 'hide';

                pin_class = ' ';
                if (mission.pinned != 1)
                    pin_class = ' btn-pin';

                private_show = ' hide';
                if (mission.private_flag == 1)
                    private_show = '';

                if (include_self)
                    html += '<li id="' + mission_html_id(mission) + '" class="list-item ' + item_class + '" data-mission-id="' + mission.mission_id + '">';
                html += '    <div class="info">'
                html += '        <span class="unreads">' + mission_unreads_to_html(mission) + '</span>'
                html += '        <a href="javascript:;" class="pin ' + pin_class + '" ng-click="pinMission(' + mission_id + ')"><i class="icon-pin"></i></a>'
                html += '    </div>'
                html += '    <a ng-href="#/chats/' + mission.mission_id + '" title="' + mission.mission_name + '"><i class="fa fa-lock' + private_show + '"></i> ' + mission.mission_name + '</a>'
                if (include_self)
                    html += '</li>'
            else if (mission.private_flag == 2)
                ###
                <li data-ng-repeat="mission in missions2 = (missions | filter:memberFilter | filter:{mission_name:search_string} | orderBy:'order') track by mission.user_id" class="list-item" ng-class="{'active': nav_id=='chatroom_' + mission.mission_id }" data-mission-id="{{mission.mission_id}}" ng-show="search_string !='' && search_string != null || groups[2] || mission.visible">
                    <img alt="" ng-src="{{mission.avartar}}" class="avartar">
                    <div class="info">
                        <i class="badge badge-danger" ng-show="mission.unreads > 0">{{mission.unreads}}</i>
                        <a href="javascript:;" ng-class="{'btn-pin': mission.pinned!=1}" ng-click="pinMission(mission)"><i class="icon-pin"></i></a>
                    </div>
                    <a href="javascript:;" ng-click="open_member(mission)" title="{{::mission.mission_name}}">{{mission.mission_name}}</a>
                </li>
                ###
                item_class = ' ';
                if ($rootScope.nav_id == 'chatroom_' + mission.mission_id)
                    item_class += 'active ';
                if (!(groups[2] || mission.visible))
                    item_class += 'hide';

                pin_class = ' ';
                if (mission.pinned != 1)
                    pin_class = ' btn-pin';

                if (include_self)
                    html += '<li id="' + mission_html_id(mission) + '" class="list-item ' + item_class + '" data-mission-id="' + mission.mission_id + '">';
                html += '    <img alt="" ng-src="' + mission.avartar + '" class="avartar">';
                html += '    <div class="info">'
                html += '        <span class="unreads">' + mission_unreads_to_html(mission) + '</span>'
                html += '        <a href="javascript:;" class="pin ' + pin_class + '" ng-click="pinMission(' + mission_id + ')"><i class="icon-pin"></i></a>'
                html += '    </div>'
                html += '    <a href="javascript:;" ng-click="open_member(' + mission.user_id + ')" title="' + mission.mission_name + '">' + mission.mission_name + '</a>'
                if (include_self)
                    html += '</li>'

            return html

        get_top_of_hidden_unreads = () ->
            parentRect = angular.element('#nav')[0].getBoundingClientRect()
            avartar = $('.nav-bar .my-avartar').height()
            logout = $('.nav-bar .logout').height()
            scroll_top = $('#nav').scrollTop()
            if $rootScope.missions
                for mission in $rootScope.missions
                    if mission.unreads > 0
                        id = mission_html_id(mission)
                        el = angular.element('#' + id)[0]
                        if el 
                            offset = el.getBoundingClientRect()
                            if offset && offset.top > parentRect.bottom - avartar - logout
                                return scroll_top + offset.top + offset.height

            return null

        check_hidden_unreads = () ->
            if get_top_of_hidden_unreads() != null
                $('.unread_hint').show()
            else
                $('.unread_hint').hide()
            return

        priv = (mission_id, user_id, priv, callback) ->
            params = 
                mission_id: mission_id
                user_id: user_id
                priv: priv

            $api.call("mission/priv", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        upload_emoticon = (mission_id, file) ->
            $api.upload_file('mission/upload_emoticon', file, {
                mission_id: mission_id
            })

        add_emoticon = (emoticon, callback) ->
            $api.call("mission/add_emoticon", emoticon)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        save_emoticon = (emoticon, callback) ->
            $api.call("mission/save_emoticon", emoticon)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        remove_emoticon = (emoticon_id, callback) ->
            params =
                emoticon_id: emoticon_id
            $api.call("mission/remove_emoticon", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        return {
            search: search
            unpinned_missions: unpinned_missions
            search_completed: search_completed
            refresh_remaining: refresh_remaining
            get_mission: get_mission
            set_mission: set_mission
            set_cur_mission: set_cur_mission
            add: add
            get_name: get_name
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
            self_invite: self_invite

            remove_attach: remove_attach
            complete: complete
            break_mission: break_mission
            set_repeat: set_repeat

            set_back_pos: set_back_pos
            upload_back_image: upload_back_image
            delete_back_image: delete_back_image

            get_bot_messages: get_bot_messages

            select_mission_in_nav: select_mission_in_nav
            mission_html_id: mission_html_id
            mission_unreads_to_html: mission_unreads_to_html
            mission_to_html: mission_to_html
            get_top_of_hidden_unreads: get_top_of_hidden_unreads
            check_hidden_unreads: check_hidden_unreads

            priv: priv

            upload_emoticon: upload_emoticon
            add_emoticon: add_emoticon
            save_emoticon: save_emoticon
            remove_emoticon: remove_emoticon
        }
)
