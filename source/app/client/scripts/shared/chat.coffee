angular.module('app.service.chat', [])

.service('$chat', 
    ($rootScope, $session, chatStorage, userStorage, $http, CONFIG, logger, 
        $location, $timeout, $interval, AUTH_EVENTS, $websocket, $api, $cache, chatizeService) ->
        $this = this
        $this.socket = null
        $this.out_queue = []
        $this.out_key = 0
        $this.client_key = Math.floor((Math.random() * 1000000) + 1)

        $this.connect = ->
            if $session.user_id == null
                return

            uri = $rootScope.chat_uri + $session.user_id + "/" + $this.client_key
            $this.socket = $websocket.$new(uri) 
            $this.socket.$$config.enqueue = true
            if $this.socket.$$config.reconnect == false
                $this.socket.$open()

            $this.socket.$on('$open', () ->
                console.log('Connected chat server')
                $rootScope.error_disconnected = false
                $rootScope.$apply()
            )

            $this.socket.$on('$error', (ev) ->
                console.log('Error Occurred ' + ev.data)
                #logger.logError("メッセージ送信が失敗しました。")
            )

            $this.socket.$on('$close', () ->
                console.log('Connection closed')
                $timeout(
                    $rootScope.error_disconnected = !($this.socket && $this.socket.$ready())
                    $rootScope.$apply()
                , 3000)
            )

            $this.socket.$on('chat_message', (cmsg) ->
                found_home = false
                $cache.get_message(cmsg.cache_id, (res) ->
                    cmsg.content = res.content

                    if $session.user_id != cmsg.user_id
                        unread = chatStorage.get_unread(cmsg)

                        delta_unreads = 0
                        delta_to_unreads = 0

                        cmsg.to_flag = chatStorage.is_to_mine(cmsg)
                        if !unread
                            delta_unreads = 1
                        if !unread || unread && !unread.to_flag
                            if cmsg.to_flag
                                delta_to_unreads = 1

                        if $rootScope.cur_home.home_id == cmsg.home_id && $rootScope.missions
                            for mission in $rootScope.missions
                                if mission.mission_id == cmsg.mission_id
                                    mission.unreads += delta_unreads
                                    mission.to_unreads += delta_to_unreads
                                    if delta_unreads > 0 || delta_to_unreads > 0
                                        chatStorage.set_unread(cmsg)
                                    mission.visible = true
                                    $rootScope.$broadcast('unread-message', mission)

                        if delta_unreads > 0 && $rootScope.homes
                            for home in $rootScope.homes
                                if home.home_id == cmsg.home_id
                                    found_home = true
                                    home.unreads += delta_unreads
                                    home.to_unreads += delta_to_unreads
                                    #$rootScope.$apply()
                                    chatStorage.sound_alert()
                                    $rootScope.$broadcast('refresh-home', home)
                    else
                        found_home = true
                    
                    chatStorage.reorder_home_mission(cmsg.home_id, cmsg.mission_id)

                    if !found_home
                        $rootScope.$broadcast('refresh-homes')

                    if $rootScope.windowState == 'hidden' && cmsg.push_flag
                        title = ''
                        if cmsg.user_name != null
                            title = cmsg.user_name + "さん  "
                        title += '(ハンドクラウド'
                        if cmsg.home_name != null
                            title += ":" + cmsg.home_name
                        title += ")"
                        $api.show_notification(CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg", title, chatizeService.strip(cmsg.content), "/chats/" + cmsg.mission_id)
                    $rootScope.$broadcast('receive_message', cmsg)
                )
            )

            $this.socket.$on('react_message', (cmsg) ->
                if $rootScope.cur_mission.mission_id == cmsg.mission_id
                    $rootScope.$broadcast('react_message', cmsg)
            )

            $this.socket.$on('remove_message', (cmsg) ->
                found_mission = false
                angular.forEach $rootScope.missions, (mission) ->
                    if mission.mission_id == cmsg.mission_id
                        found_mission = true
                if !found_mission
                    $rootScope.$broadcast('refresh-homes')
                $rootScope.$broadcast('remove_message', cmsg)                
            )

            $this.socket.$on('alert', (cmsg) ->
                $rootScope.$broadcast('alert', cmsg)
                $rootScope.$apply(->
                    userStorage.alerts()
                )
            )

            $this.socket.$on('task', (msg) ->
                if $rootScope.cur_mission != null && $rootScope.cur_mission.mission_id == msg.mission_id
                    $rootScope.$broadcast('refresh-tasks', msg)
            )

            $this.socket.$on('mission', (msg) ->
                if $rootScope.cur_home != null && $rootScope.cur_home.home_id == msg.home_id
                    $rootScope.$broadcast('refresh-missions')
            )

            $this.socket.$on('home', (msg) ->
                if msg.type == "refresh-logo"
                    $rootScope.$broadcast('refresh-home-logo', msg.home_id)
                    return
                if $rootScope.cur_home != null && $rootScope.cur_home.home_id == msg.home_id
                    if msg.type == "remove_member" && msg.user_id == $session.user_id || msg.type == "remove"
                        logger.logSuccess("グループから削除されました。")
                        $rootScope.$broadcast('removed_home')
                    else if msg.type == "accept_invite"
                        $rootScope.$broadcast('refresh-missions')
                    else
                        $rootScope.$broadcast('refresh-homes', msg)
                else
                    $rootScope.$broadcast('refresh-homes', msg)
            )

            $this.socket.$on('ok', (msg) ->
                i = 0
                for m in $this.out_queue
                    if m.key == msg.key
                        $this.out_queue.splice(i, 1);
                        break;
                    i = i + 1
            )
            return;

        $this.disconnect = ->
            if ($this.socket != null)
                $this.socket.$un('$open')
                $this.socket.$un('$error')
                $this.socket.$un('$close')
                $this.socket.$un('chat_message')
                $this.socket.$un('remove_message')
                $this.socket.$close()
                $this.socket = null

        $this.retryConnect = ->
            $timeout( ->
                $this.connect()
            , 3000)

        $this.onLogin = ->
            if $this.socket != null
                $this.disconnect()

            $this.connect()

        $this.send = (cmsg_id, home_id, mission_id, content, to_id, is_file) ->
            if $this.socket != null
                $cache.set_message(null, content, (res) ->
                    cache_id = res.cache_id
                    if cache_id
                        msg = 
                            cmsg_id: cmsg_id
                            home_id: home_id
                            mission_id: mission_id
                            cache_id: cache_id
                            to_id: to_id
                            is_file: is_file
                            home_name: $rootScope.cur_home.home_name
                        $this.emit('chat_message', msg)
                )

        $this.react = (cmsg_id, mission_id, emoticon_id) ->
            if $this.socket != null
                msg = 
                    cmsg_id: cmsg_id
                    mission_id: mission_id
                    emoticon_id: emoticon_id
                $this.emit('react_message', msg)

        $this.remove = (cmsg_id, mission_id) ->
            if $this.socket != null
                msg = 
                    cmsg_id: cmsg_id
                    mission_id: mission_id
                $this.emit('remove_message', msg)

        $this.alert = (alert_type, user_id, info) ->
            if $this.socket != null
                msg = 
                    alert_type: alert_type
                    user_id: user_id
                    info: info
                $this.emit('alert', msg)

        $this.task = (type, task_id, mission_id) ->
            if $this.socket != null
                msg = 
                    type: type
                    task_id: task_id
                    mission_id: mission_id
                $this.emit('task', msg)

        $this.mission = (type, mission_id, home_id) ->
            if $this.socket != null
                msg = 
                    type: type
                    mission_id: mission_id
                    home_id: home_id
                $this.emit('mission', msg)

        $this.home = (type, home_id, user_id) ->
            if $this.socket != null
                msg = 
                    type: type
                    home_id: home_id
                    user_id: user_id
                $this.emit('home', msg)

        $this.bot_message = (home_id) ->
            if $this.socket != null
                msg = 
                    home_id: home_id
                $this.emit('bot_message', msg)

        $this.emit = (evt, msg) ->
            if msg.key == undefined && evt != 'alive'
                $this.out_key = $this.out_key + 1
                msg.key = $this.out_key
                msg.event = evt
                $this.out_queue.push(msg)
            $this.socket.$emit(evt, msg)
            $timeout(() ->
                $this.resend(msg)
            , 2000)

        $this.resend = (msg) ->
            for m in $this.out_queue
                if m.key == msg.key # check fail of send
                    # resend
                    console.log("resend message")
                    $this.emit(m.event, m)
            
        $rootScope.$on('reload_session', $this.onLogin)
        $rootScope.$on(AUTH_EVENTS.loginSuccess, $this.onLogin)

        $rootScope.$on('closed_session', ->            
            if $this.socket != null
                $this.disconnect()
        )

        $interval(->
            if $this.socket && $this.socket.$ready()
                msg = 
                    time: new Date().getTime()
                $this.emit('alive', msg)
        , 15000)

        return $this
)
