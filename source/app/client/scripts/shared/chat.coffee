angular.module('app.service.chat', [])

.service('$chat', 
    ($rootScope, $session, chatStorage, userStorage, $http, 
        CONFIG, logger, $location, $timeout, AUTH_EVENTS, $websocket, $api, chatizeService) ->
        $this = this
        $this.socket = null
        $this.out_queue = []
        $this.out_key = 0

        $this.connect = ->
            if $session.user_id == null
                return

            uri = $rootScope.chat_uri + $session.user_id
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
                $rootScope.error_disconnected = true
                $rootScope.$apply()
                #$this.retryConnect()
            )

            $this.socket.$on('chat_message', (cmsg) ->
                found_mission = false
                if $rootScope.cur_home.home_id == cmsg.home_id
                    angular.forEach $rootScope.missions, (mission) ->
                        if mission.mission_id == cmsg.mission_id
                            found_mission = true
                            if $session.user_id != cmsg.user_id
                                mission.unreads++
                                mission.visible=true
                                $rootScope.$apply()
                        return

                angular.forEach $rootScope.homes, (home) ->
                    if home.home_id == cmsg.home_id
                        if $session.user_id != cmsg.user_id
                            home.unreads++
                            $rootScope.$apply()
                            chatStorage.sound_alert()
                    return

                chatStorage.reorder_home_mission(cmsg.home_id, cmsg.mission_id)

                if !found_mission
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

            $this.socket.$on('remove_message', (cmsg) ->
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
                        logger.logSuccess("ホームから削除されました。")
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

        $this.send = (cmsg_id, mission_id, content, to_id, is_file) ->
            if $this.socket != null
                msg = 
                    cmsg_id: cmsg_id
                    mission_id: mission_id
                    content: content
                    to_id: to_id
                    is_file: is_file
                    home_name: $rootScope.cur_home.home_name
                $this.emit('chat_message', msg)

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
            if msg.key == undefined
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

        return $this
)
