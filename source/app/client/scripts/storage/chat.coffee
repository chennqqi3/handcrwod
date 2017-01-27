'use strict'

angular.module('app.storage.chat', [])

.factory('chatStorage', 
    ($api, $session, $dateutil, $rootScope, CONFIG, $filter, chatizeService) ->
        cache_messages = (mission_id, messages) ->
            if $rootScope.g_messages == undefined
                $rootScope.g_messages = []

            if messages == undefined
                # read
                messages = $rootScope.g_messages[mission_id]
                if messages == undefined
                    messages = []
            else
                if messages == null
                    messages = []
                if messages.length > 200
                    tmp = messages.splice(messages.length - 200)
                else
                    tmp = messages.splice()

                # write
                $rootScope.g_messages[mission_id] = tmp

            return messages

        is_to_mine = (message) ->
            if $api.is_empty(message)
                return false

            if $api.is_empty(message.content)
                return false

            mine = false
            message.content += ""
            message.content.replace(/\[to:([^\]]*)\]/g, (item, user_id) ->
                if $session.user_id + '' == user_id || 'all' == user_id
                    mine = true
            )

            return mine

        ###
            <div ng-repeat="message in messages track by message.cmsg_id" class="message-wrapper" ng-class="{'to-mine': isToMine(message), 'linked': message.cmsg_id==chat_id}">
                <div id="chat_{{message.cmsg_id}}">
                    <img class="avartar clickable left" ng-src="{{::message.avartar}}" ng-if="message.show_avartar" ng-click="showUserProfile(message.user_id)"/>

                    <div class="chat-message left" ng-class="{'me': session.user_id == message.user_id, 'border-top': message.show_avartar, 'editing': message.editing}">
                        <div class="sending" ng-if="message.cmsg_id < 0"><span>メッセージ送信中 <i class="fa fa-spinner fa-spin"></i></span></div>
                        <div class="message-detail">
                            <span ng-click="viewProfile(message)" class="user-name" ng-if="message.show_avartar">{{::message.user_name}}</span> 
                            <span class="time">{{::message.date_label}}</span>
                            <a href="javascript:;" class="star" ng-class="{'show-hover': !message.star}" ng-click="star(message)" title="スター付き"><i class="fa text-warning" ng-class="{'fa-star': message.star, 'fa-star-o':!message.star}"></i></a>
                            <span class="unread-mark"><i class="fa fa-circle text-danger {{message.read_class}}"></i></span>
                        </div>
                        <div class="message" ng-bind-html="message.content | chatize">
                        </div>
                    </div>
                </div>

                <ul class="button-group" ng-class="{'editing': message.editing}" ng-if="message.cmsg_id>0">
                    <li ng-if="canEditTask()">
                        <button type="button" class="btn btn-default btn-xs btn-circle" title="タスク新規登録" ng-click="addTask(message)"><i class="ln-icon-check-square"></i></button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-default btn-xs btn-circle" title="リンク" ng-click="link(message)"><i class="icon-link"></i></button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-default btn-xs btn-circle" title="引用" ng-click="quote(message)"><i class="ln-icon-quote-open"></i></button>
                    </li>
                    <li ng-if="session.user_id == message.user_id">
                        <button type="button" class="btn btn-default btn-xs btn-circle" title="編集" ng-click="edit(message)"><i class="icon-pencil"></i></button>
                    </li>
                    <li ng-if="session.user_id == message.user_id">
                        <button type="button" class="btn btn-default btn-xs btn-circle" title="削除" ng-click="remove(message)"><i class="icon-trash"></i></button>
                    </li>
                </ul>

                <div class="clear"></div>
            </div>
        ###
        message_to_html = (message, chat_id, uninclude_self) ->
            html = ""

            cls = ""
            if is_to_mine(message)
                cls += ' to-mine'
            if message.cmsg_id == chat_id
                cls += ' linked'

            avartar_cls = ""
            if !message.show_avartar
                avartar_cls += " hide"

            message_cls = ""
            if $session.user_id == message.user_id
                message_cls += ' me'
            if message.show_avartar
                message_cls += ' border-top'
            if message.editing
                message_cls += ' editing'

            sending_cls = ""
            if message.cmsg_id >= 0
                sending_cls += " hide"

            username_cls = ""
            if !message.show_avartar
                username_cls += " hide"

            if message.star
                star_cls = ""
                star_fa_cls = " fa-star"
            else
                star_cls = " show-hover"
                star_fa_cls = " fa-star-o"

            date_label = message.date_label
            if date_label == undefined
                date_label = ''

            btn_group_cls = ' ' + message.editing
            if message.cmsg_id < 0
                btn_group_cls += ' hide'

            if uninclude_self != true
                html += '<div id="chat_' + message.cmsg_id + '" >'
            html += '<div class="message-wrapper' + cls + '" data-cmsg_id="' + message.cmsg_id + '">'
            html += '   <img class="avartar clickable left' + avartar_cls + '" src="' + message.avartar + '" onerror="this.src = \'\';" ng-click="showUserProfile(\'' + message.user_id + '\')"/>'
            html += '   <div class="chat-message left' + message_cls + '">'
            html += '       <div class="sending' + sending_cls + '"><span>メッセージ送信中 <i class="fa fa-spinner fa-spin"></i></span></div>'
            html += '       <div class="message-detail">'
            html += '           <span class="user-name' + username_cls + '" ng-click="showUserProfile(' + message.user_id + ')">' + message.user_name + '</span>'
            html += '           <span class="time">' + date_label + '</span>'
            html += '           <a href="javascript:;" class="star' + star_cls + '" ng-click="star(' + message.cmsg_id + ')" title="スター付き"><i class="fa text-warning' + star_fa_cls + '"></i></a>'
            html += '           <span class="unread-mark"><i class="fa fa-circle text-danger ' + message.read_class + '"></i></span>'
            html += '       </div>'
            html += '       <div class="message">' + $filter('chatize')(message.content)
            if message.reacts && message.reacts.length > 0
                html += '       <ul class="reacts">'
                for react in message.reacts
                    rtitle = ""
                    if react[2]
                        for u in react[2]
                            if rtitle != ""
                                rtitle += ","
                            rtitle += u[1]
                        rtitle = ' title="' + rtitle + '"'
                    html += '       <li ng-click="react(' + message.cmsg_id + ', ' + react[0] + ')" ' + rtitle + '>' + chatizeService.emoticon(react[0]) + react[1] + '</li>'    
                html += '       </ul>'
            html += '       </div>'
            html += '   </div>'
            html += '   <ul class="button-group' + btn_group_cls + '">'
            html += '       <li>'
            html += '           <button type="button" class="btn btn-default btn-xs btn-circle btn-react" title="リアクション" ng-click="add_react(' + message.cmsg_id + ', $event)"><i class="icon-emotsmile"></i></button>'
            html += '       </li>'
            if $rootScope.canEditTask()
                html += '   <li>'
                html += '       <button type="button" class="btn btn-default btn-xs btn-circle" title="タスク新規登録" ng-click="addTask(' + message.cmsg_id + ')"><i class="ln-icon-check-square"></i></button>'
                html += '   </li>'
            html += '       <li>'
            html += '           <button type="button" class="btn btn-default btn-xs btn-circle" title="リンク" ng-click="link(' + message.cmsg_id + ')"><i class="icon-link"></i></button>'
            html += '       </li>'
            html += '       <li>'
            html += '           <button type="button" class="btn btn-default btn-xs btn-circle" title="引用" ng-click="quote(' + message.cmsg_id + ')"><i class="ln-icon-quote-open"></i></button>'
            html += '       </li>'
            if $session.user_id == message.user_id
                html += '   <li>'
                html += '       <button type="button" class="btn btn-default btn-xs btn-circle" title="編集" ng-click="edit(' + message.cmsg_id + ')"><i class="icon-pencil"></i></button>'
                html += '   </li>'
                html += '   <li>'
                html += '       <button type="button" class="btn btn-default btn-xs btn-circle" title="削除" ng-click="remove(' + message.cmsg_id + ')"><i class="icon-trash"></i></button>'
                html += '   </li>'
            html += '       <li>'
            html += '           <button type="button" class="btn btn-default btn-xs btn-circle" title="未読にする" ng-click="unread(' + message.cmsg_id + ', $event)">未読</button>'
            html += '       </li>'
            html += '   </ul>'
            html += '   <div class="clear"></div>'
            html += '</div>'
            if uninclude_self != true
                html += '</div>'

            return html

        messages_to_html = (messages, chat_id) ->
            html = ""
            messages.forEach((message) ->
                html += message_to_html(message, chat_id)
            )

            return html

        # Search messages
        messages = (mission_id, prev_id, next_id, star) ->
            params = 
                home_id: $rootScope.cur_home.home_id
                mission_id: mission_id
                prev_id: prev_id
                next_id: next_id
                star: star
                limit: 60

            $api.call("chat/messages", params)
                .then((res) ->
                    if res.data.err_code == 0
                        messages = res.data.messages
                        user_id = null
                        prev_date = null
                        messages.forEach((cmsg) ->
                            cmsg.content = cmsg.content + ''
                            cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg"
                            cmsg.read_class = (if cmsg.unread then "unread" else "read")
                            date_label = $dateutil.ellipsis_time_str(cmsg.date, prev_date)
                            prev_date = cmsg.date
                            cmsg.date_label = date_label
                            if cmsg.user_id != user_id
                                cmsg.show_avartar = true
                                user_id = cmsg.user_id
                            else
                                cmsg.show_avartar = false
                        )

                        $rootScope.read_message_offset = 0
                        refresh_unreads_title()
                        return messages
                    else
                        return []
                )

        # Search message with string
        search_messages = (home_id, mission_id, search_string, prev_id, next_id) ->
            params = 
                home_id: home_id
                mission_id: mission_id
                search_string: search_string
                prev_id: prev_id
                next_id: next_id

            $api.call("chat/search_messages", params)
                .then((res) ->
                    if res.data.err_code == 0
                        messages = res.data.messages
                        messages.forEach((cmsg) ->
                            cmsg.content = cmsg.content + ''
                            cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg"
                            cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null)
                            if $rootScope.cur_home.home_id != cmsg.home_id && !$api.is_empty(cmsg.home_name)
                                cmsg.mission_name = cmsg.mission_name + '(' + cmsg.home_name + ')'
                        )

                        return messages
                    else
                        return []
                )

        # messages with star
        star_messages = (prev_id, next_id) ->
            params = 
                prev_id: prev_id
                next_id: next_id
                limit: 60

            $api.call("chat/star_messages", params)
                .then((res) ->
                    if res.data.err_code == 0
                        messages = res.data.messages
                        messages.forEach((cmsg) ->
                            cmsg.content = cmsg.content + ''
                            cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg"
                            cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null)
                            if $rootScope.cur_home.home_id != cmsg.home_id && !$api.is_empty(cmsg.home_name)
                                cmsg.mission_name = cmsg.mission_name + '(' + cmsg.home_name + ')'
                        )

                        return messages
                    else
                        return []
                )

        read_messages = (mission_id, cmsg_ids) ->
            params =
                mission_id: mission_id
                cmsg_ids: cmsg_ids

            $api.call("chat/read_messages", params)
                .then((res) ->
                    return res.data
                )

        unread_messages = (mission_id, cmsg_ids) ->
            params =
                mission_id: mission_id
                cmsg_ids: cmsg_ids

            $api.call("chat/unread_messages", params)
                .then((res) ->
                    return res.data
                )

        delete_message = (messages, cid) -> 
            if $api.is_empty(messages)
                return

            i = 0
            for i in [messages.length-1..0] by -1
                message = messages[i]
                if message.cmsg_id == cid
                    messages.splice(i, 1)
                    $('#chat_' + cid).remove()
            return

        set_message = (mission_id, messages, cmsg, callback) ->
            cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg"
            cmsg.unread = $session.user_id != cmsg.user_id
            cmsg.read_class = (if cmsg.unread then "unread" else "read")

            if cmsg.cmsg_id < 0
                if messages.length > 0
                    l_cmsg = messages[messages.length - 1]
                    if l_cmsg.user_id == cmsg.user_id 
                        cmsg.show_avartar = false
                    else
                        cmsg.show_avartar = true
                else
                    cmsg.show_avartar = true
                messages.push(cmsg)
                if (callback)
                    callback(cmsg)
            else
                setted = false
                if !$api.is_empty(cmsg.temp_cmsg_id)
                    delete_message(messages, cmsg.cmsg_id) # check if my message is coming after next()
                if messages.length > 0                       
                    for i in [0..messages.length - 1]
                        if messages[i].cmsg_id == cmsg.temp_cmsg_id
                            messages[i].cmsg_id = cmsg.cmsg_id

                        if messages[i].cmsg_id == cmsg.cmsg_id
                            cmsg.inserted = false # from self 
                            messages[i] = cmsg
                            setted = true
                            if i == 0
                                cmsg.show_avartar = true
                                cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null) 
                            else
                                prev_message = messages[i-1]
                                if prev_message.user_id != cmsg.user_id
                                    cmsg.show_avartar = true
                                cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, prev_message.date) 

                            setted = true

                            if (callback)
                                callback(cmsg)
                            break

                if cmsg.inserted && setted == false # from other user
                    if messages.length > 0
                        l_cmsg = messages[messages.length - 1]
                        if l_cmsg.user_id == cmsg.user_id 
                            cmsg.show_avartar = false
                            cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, l_cmsg.date)
                        else
                            cmsg.show_avartar = true
                            cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null) 
                    else
                        cmsg.show_avartar = true
                        cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null) 
                    messages.push(cmsg)

                    if (callback)
                        callback(cmsg)

            cache_messages(mission_id, messages)
            return

        remove_message = (messages, cmsg) ->
            for i in [0..messages.length - 1]
                message = messages[i]
                if message.cmsg_id == cmsg.cmsg_id
                    if i < messages.length-1
                        next_message = messages[i+1]
                        if message.show_avartar 
                            next_message.show_avartar = true
                        if i>0
                            prev_message = messages[i-1]
                            next_message.date_label = $dateutil.ellipsis_time_str(next_message.date, prev_message.date) 
                        else
                            next_message.date_label = $dateutil.ellipsis_time_str(next_message.date, null)
                    messages.splice(i, 1)
                    break
            return

        star_message = (cmsg_id, star) ->
            params =
                cmsg_id: cmsg_id
                star: star

            $api.call("chat/star_message", params)
                .then((res) ->
                    if res.data.err_code == 0
                        $rootScope.$broadcast('refresh-star')
                    return res.data
                )

        upload_file = (mission_id, file) ->
            $api.upload_file('mission/upload_attach', file, {
                mission_id: mission_id
            })

        cancel_upload_file = (file) ->
            $api.cancel_upload(file.upload)
            return

        get_unread = (cmsg) ->
            mission_id = cmsg.mission_id
            unreads = $rootScope.unreads[mission_id]
            if unreads
                for u in unreads
                    if u.cmsg_id == cmsg.cmsg_id
                        return u

            return null

        set_unread = (cmsg) ->
            unread = get_unread(cmsg)
            if unread
                unread.to_flag = cmsg.to_flag
            else
                mission_id = cmsg.mission_id
                unreads = $rootScope.unreads[mission_id]
                if unreads == undefined
                    $rootScope.unreads[mission_id] = []
                    unreads = $rootScope.unreads[mission_id]
                
                unreads.push(
                    cmsg_id: cmsg.cmsg_id
                    to_flag: cmsg.to_flag
                )
            return

        remove_unread = (cmsg) ->
            mission_id = cmsg.mission_id
            unreads = $rootScope.unreads[mission_id]
            if unreads
                for u, i in unreads
                    if u.cmsg_id == cmsg.cmsg_id
                        unreads.splice(i, 1)
                        break

        # Refresh unreads
        refresh_unreads_title = (refresh_home) ->
            if refresh_home == undefined
                refresh_home = true
            unreads = 0
            to_unreads = 0
            angular.forEach $rootScope.missions, (mission) ->
                if mission.unreads > 0
                    unreads += mission.unreads
                if mission.to_unreads > 0
                    to_unreads += mission.to_unreads
                return

            if $rootScope.homes
                t_unreads = 0
                t_to_unreads = 0
                for home in $rootScope.homes
                    if $rootScope.cur_home && home.home_id == $rootScope.cur_home.home_id
                        home.unreads = unreads
                        home.to_unreads = to_unreads
                        if refresh_home
                            $rootScope.$broadcast('refresh-home', home)
                    t_unreads += home.unreads
                    t_to_unreads += home.to_unreads

            title = ""
            if t_unreads > 0
                title = "[" + t_unreads 
                if t_to_unreads > 0
                    title += "(" + t_to_unreads + ")"
                title += "]"

            document.title = title + "ハンドクラウド"
            return

        reorder_home_mission = (last_home_id, last_mission_id) ->
            if last_home_id != undefined
                order = 0
                for home in $rootScope.homes 
                    if home.order < order && home.home_id != last_home_id
                        order = home.order
                for home in $rootScope.homes 
                    if home.home_id == last_home_id
                        home.order = order - 1

            if last_mission_id != undefined
                order = 0
                for mission in $rootScope.missions 
                    if mission.order < order && mission.mission_id != last_mission_id
                        order = mission.order
                for mission in $rootScope.missions 
                    if mission.mission_id == last_mission_id
                        mission.order = order - 1

            refresh_unreads_title(false)

        sound_alert = ->
            audioElement = document.createElement('audio')

            if (navigator.userAgent.match('Firefox/'))
                audioElement.setAttribute('src', 'sound/alert.ogg')
            else
                audioElement.setAttribute('src', 'sound/alert.mp3')

            $.get()
            audioElement.addEventListener("load", ->
                audioElement.play()
            , true)

            audioElement.pause()
            audioElement.play()

        # Search read messages
        search_read = () ->
            params = {}
                
            params.offset = $rootScope.read_message_offset
            params.limit = 10

        return {
            cache_messages: cache_messages
            is_to_mine: is_to_mine
            message_to_html: message_to_html
            messages_to_html: messages_to_html
            messages: messages
            search_messages: search_messages
            star_messages: star_messages
            read_messages : read_messages
            unread_messages: unread_messages
            delete_message: delete_message
            set_message: set_message
            remove_message: remove_message
            star_message: star_message
            upload_file: upload_file
            cancel_upload_file: cancel_upload_file
            get_unread: get_unread
            set_unread: set_unread
            remove_unread: remove_unread
            refresh_unreads_title: refresh_unreads_title
            reorder_home_mission: reorder_home_mission
            sound_alert: sound_alert
            search_read: search_read
        }
)
