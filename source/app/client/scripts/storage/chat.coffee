'use strict'

angular.module('app.storage.chat', [])

.factory('$emoticons', ->
    icons = [ 
        (class:'emoticon-smile', title:'笑顔', alt:':)')
        (class:'emoticon-sad', title:'悲しい', alt:':(')
        (class:'emoticon-more-smile', title:'もっとスマイル', alt:':D')
        (class:'emoticon-lucky', title:'やったね', alt:'8-)')
        (class:'emoticon-surprise', title:'びっくり', alt:':o')
        (class:'emoticon-wink', title:'ウィンク', alt:';)')
        (class:'emoticon-tears', title:'ウェ～ん', alt:';(')
        (class:'emoticon-sweat', title:'汗', alt:'(sweat)')
        (class:'emoticon-mumu', title:'むむ', alt:':|')
        (class:'emoticon-kiss', title:'チュ！', alt:':*')
        (class:'emoticon-tongueout', title:'べー', alt:':p')
        (class:'emoticon-blush', title:'恥ずかしい', alt:'(blush)')
        (class:'emoticon-wonder', title:'何なに', alt:':^)')
        (class:'emoticon-snooze', title:'眠い', alt:'|-)')
        (class:'emoticon-love', title:'恋してます', alt:'(inlove)')
        (class:'emoticon-grin', title:'ニヤッ', alt:']:)')
        (class:'emoticon-talk', title:'話す', alt:'(talk)')
        (class:'emoticon-yawn', title:'あくび', alt:'(yawn)')
        (class:'emoticon-puke', title:'ゲーッ', alt:'(puke)')
        (class:'emoticon-ikemen', title:'イケメン', alt:'(emo)')
        (class:'emoticon-otaku', title:'オタク', alt:'8-|')
        (class:'emoticon-ninmari', title:'ニンマリ', alt:':#)')
        (class:'emoticon-nod', title:'うんうん', alt:'(nod)')
        (class:'emoticon-shake', title:'いやいや', alt:'(shake)')
        (class:'emoticon-wry-smile', title:'苦笑い', alt:'(^^;)')
        (class:'emoticon-whew', title:'やれやれ', alt:'(whew)')
        (class:'emoticon-clap', title:'拍手', alt:'(clap)')
        (class:'emoticon-bow', title:'おじぎ', alt:'(bow)')
        (class:'emoticon-roger', title:'了解！', alt:'(roger)')
        (class:'emoticon-muscle', title:'筋肉モリモリ', alt:'(flex)')
        (class:'emoticon-dance', title:'ダンス', alt:'(dance)')
        (class:'emoticon-komanechi', title:'コマネチ', alt:'(:/)')
        (class:'emoticon-devil', title:'悪魔', alt:'(devil)')
        (class:'emoticon-star', title:'星', alt:'(*)')
        (class:'emoticon-heart', title:'ハート', alt:'(h)')
        (class:'emoticon-flower', title:'花', alt:'(F)')
        (class:'emoticon-cracker', title:'クラッカー', alt:'(cracker)')
        (class:'emoticon-cake', title:'ケーキ', alt:'(^)')
        (class:'emoticon-coffee', title:'コーヒー', alt:'(coffee)')
        (class:'emoticon-beer', title:'ビール', alt:'(beer)')
        (class:'emoticon-handshake', title:'握手', alt:'(handshake)')
        (class:'emoticon-yes', title:'はい', alt:'(y)')
    ]

    for icon in icons 
        icon.exp = icon.alt.replace(/\)/g, '\\)')
        icon.exp = icon.exp.replace(/\(/g, '\\(')
        icon.exp = icon.exp.replace(/\:/g, '\\:')
        icon.exp = icon.exp.replace(/\|/g, '\\|')
        icon.exp = icon.exp.replace(/\*/g, '\\*')
        icon.exp = icon.exp.replace(/\^/g, '\\^')
        icon.exp = new RegExp(icon.exp, 'g')

    return {
        icons: icons
    }
)

.factory('chatStorage', 
    ($api, $session, $dateutil, $rootScope, filterFilter, AUTH_EVENTS, $auth, CONFIG) ->
        # Initialize
        init = ->
            ###
            if $auth.isAuthenticated()
                search()
            ###

        cache_messages = (mission_id, messages) ->
            if $rootScope.g_messages == undefined
                $rootScope.g_messages = []

            if messages == undefined
                # read
                messages = $rootScope.g_messages[mission_id]
                if messages == undefined
                    messages = []
            else
                # write
                $rootScope.g_messages[mission_id] = messages

            return messages

        # Search messages
        messages = (mission_id, prev_id, next_id, star) ->
            params = 
                home_id: $rootScope.cur_home.home_id
                mission_id: mission_id
                prev_id: prev_id
                next_id: next_id
                star: star

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
                        user_id = null
                        prev_date = null
                        mission_id = null
                        messages.forEach((cmsg) ->
                            cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg"
                            date_label = $dateutil.ellipsis_time_str(cmsg.date, prev_date)
                            prev_date = cmsg.date
                            cmsg.date_label = date_label

                            if cmsg.mission_id != mission_id || cmsg.user_id != user_id
                                cmsg.show_avartar = true
                                user_id = cmsg.user_id
                                mission_id = cmsg.mission_id
                            else
                                cmsg.show_avartar = false
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

        delete_message = (messages, cid) -> 
            if $api.is_empty(messages)
                return

            i = 0
            for i in [messages.length-1..0] by -1
                message = messages[i]
                if message.cmsg_id == cid
                    messages.splice(i, 1)
                    break
            return

        set_message = (mission_id, messages, cmsg) ->
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
            else
                setted = false
                if cmsg.temp_cmsg_id != null && cmsg.temp_cmsg_id != undefined
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
                            break

                if cmsg.inserted && setted == false # from other user
                    if messages.length > 0
                        l_cmsg = messages[messages.length - 1]
                        if l_cmsg.user_id == cmsg.user_id 
                            cmsg.show_avartar = false
                        else
                            cmsg.show_avartar = true
                    else
                        cmsg.show_avartar = true
                    messages.push(cmsg)

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

        # Refresh unreads
        refresh_unreads_title = () ->
            unread_missions = 0
            unreads = 0
            angular.forEach $rootScope.missions, (mission) ->
                if mission.unreads > 0
                    unread_missions++
                    unreads += mission.unreads
                return

            if $rootScope.homes
                for home in $rootScope.homes
                    if home.home_id == $rootScope.cur_home.home_id
                        home.unreads = unreads

            title = ""
            if unread_missions > 0
                title = "[" + unread_missions + "]"

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

            refresh_unreads_title()

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
            init: init
            cache_messages: cache_messages
            messages: messages
            search_messages: search_messages
            read_messages : read_messages
            delete_message: delete_message
            set_message: set_message
            remove_message: remove_message
            star_message: star_message
            upload_file: upload_file
            cancel_upload_file: cancel_upload_file
            refresh_unreads_title: refresh_unreads_title
            reorder_home_mission: reorder_home_mission
            sound_alert: sound_alert
            search_read: search_read
        }
)
