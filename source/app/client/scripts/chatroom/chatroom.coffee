read_timer = 2000

angular.module('app.chatroom', [])

.controller('chatroomCtrl', 
    ($scope, $api, $chat, missionStorage, chatStorage, taskStorage, homeStorage, CONFIG, filterFilter, $rootScope, $routeParams, logger, $session, $dateutil, $timeout, $dialogs, $emoticons, $location, $window, $compile, $filter) ->
        $rootScope.nav_id = "chatroom_" + $routeParams.mission_id
        $scope.show_tasks = false
        $scope.show_process = false
        $scope.show_attach = false
        $scope.show_star = false
        $scope.show_summary = false
        $scope.emoticons = $emoticons.icons
        $scope.last_cid = null
        $scope.loaded_messages = false
        $scope.max_right_panel = false
        $scope.old_sideWidth = 0
        $scope.all_avartar = CONFIG.AVARTAR_URL + "all.jpg"

        MAX_MSG_LENGTH = 100

        $rootScope.taskMode = 2

        if $rootScope.cmsg_sn == undefined
            $rootScope.cmsg_sn = -1
        $scope.init_cmsg = ->
            try 
                msg = localStorage.getItem('r' + $scope.mission_id + '_' + $session.user_id)
            catch err
                msg = ''
                
            if msg == undefined || msg == 'undefined'
                msg = ''

            $scope.cmsg = 
                cmsg_id: $rootScope.cmsg_sn
                user_id: $session.user_id
                user_name: $session.user_name
                content: msg

            $rootScope.cmsg_sn -= 1

        $scope.clear_cmsg = (clear_storage) ->
            $scope.cmsg = 
                cmsg_id: $rootScope.cmsg_sn
                user_id: $session.user_id
                user_name: $session.user_name
                content: ''

            $rootScope.cmsg_sn -= 1

            if clear_storage == true
                $scope.save_in_storage()
            return                

        $scope.save_in_storage = () ->
            try 
                key = 'r' + $scope.mission_id + '_' + $session.user_id
                if $scope.cmsg == null || $scope.cmsg == undefined || $scope.cmsg.content == ''
                    localStorage.removeItem(key)
                else
                    localStorage.setItem(key, $scope.cmsg.content)
            catch err
            
            return

        $scope.clear_cmsg()

        $scope.focusInput = ->
            try
                chat_ta = document.getElementById('chat_ta')
                chat_ta.focus()
            catch err

        $scope.get_message = (cmsg_id) ->
            if $scope.messages
                for msg in $scope.messages
                    if msg.cmsg_id == cmsg_id
                        return msg
            return null

        $scope.load_messages = (messages) ->
            $scope.messages = messages
            ## refresh message list
            $scope.loaded = false
            $timeout(->
                $scope.loaded = true
            )

            length = $scope.messages.length
            if length > 0
                $scope.last_cid = $scope.messages[length-1].cmsg_id
            else
                $scope.last_cid = null

            if !isNaN($scope.chat_id) && !$api.is_empty($scope.chat_id)
                $timeout(->
                    $scope.scrollToMessage($scope.chat_id)
                , 1000)
            else
                $scope.loaded_messages = true
                $scope.scrollToBottom(false)
            $scope.startReadTimer()
            taskStorage.search($scope.mission_id).then((tasks) ->
                ##if tasks.length > 0 && !$scope.show_process
                ##    $scope.showProcess()
            )

        # Emoticon
        $rootScope.$on('$viewContentLoaded', ->
            if ($('.chat-main #emoticon_gallery').length > 0)
                $('body > #emoticon_gallery').remove()
                $('.chat-main #emoticon_gallery').appendTo($('body'))

            if ($('.chat-main #to_users').length > 0)
                $('body > #to_users').remove()
                $('.chat-main #to_users').appendTo($('body'))

            $('body > #selection_menu').hide()
            if ($('.chat-main #selection_menu').length > 0) 
                $('body > #selection_menu').remove()
                $('.chat-main #selection_menu').appendTo($('body'))   

            $('body').off("dragenter")
            $('body').off("dragleave")
            $('body').off('dragover')
            $('body').off('drop')
            $('body').off('click')
            $('body').off('mouseup')
            $('body').off('mousedown')

            $('body').on('mouseup', (e) ->
                if $('.chat-main').length > 0
                    if $rootScope.canChat()
                        sel = window.getSelection()
                        if (!sel.isCollapsed)
                            r = sel.getRangeAt(0).getBoundingClientRect()
                            #rb1 = rel1.getBoundingClientRect()
                            #rb2 = rel2.getBoundingClientRect()
                            #top = (r.bottom - rb2.top)*100/(rb1.top-rb2.top) + 'px'
                            #left = (r.left - rb2.left)*100/(rb1.left-rb2.left) + 'px'
                            $('#selection_menu').css('left', r.left + 20).css('top', r.bottom).show()
                        else
                            $('#selection_menu').hide()
                else
                    $('body').off('mouseup')
                    $('#selection_menu').remove()
            )

            $('body').on('click', (e) ->
                $('#input_bar .footer-btn-wrap .btn-smile').each(() ->
                    if !$(this).is(e.target) && $(this).has(e.target).length == 0 && $('.popover').has(e.target).length == 0
                        $('#emoticon_gallery').hide()
                        $(this).data('isShowing', "false")
                )

                $('#input_bar .footer-btn-wrap .btn-to').each(() ->
                    if !$(this).is(e.target) && $(this).has(e.target).length == 0 && $('.popover').has(e.target).length == 0
                        $('#to_users').hide()
                        $(this).data('isShowing', "false")
                )

                if $('#file_drop').has(e.target).length == 0
                    $('#file_drop').removeClass("dragover")
                    $('#file_drop').hide()
            )

            $.fn.dndhover = (options) ->
                this.each(() ->
                    self = $(this)
                    collection = $()

                    self.on("dragenter", (event) ->
                        if collection.size() == 0
                            self.trigger("dndHoverStart") 
                        collection = collection.add(event.target)
                    )
                    self.on("dragleave", (event) ->                        
                        # Firefox 3.6 fires the dragleave event on the previous element
                        # before firing dragenter on the next one so we introduce a delay
                        $timeout(->
                            collection = collection.not(event.target)
                            if collection.size()==0
                                self.trigger("dndHoverEnd")
                        , 1)
                    )
                    self.on("drop", (event) -> 
                        collection = $()
                    )
                )            

            $('body').dndhover().on('dndHoverStart', (e) ->
                e.stopPropagation()
                e.preventDefault()

                if $('#input_bar .footer-btn-wrap .btn-file')[0]
                    btn_rect = $('#input_bar .footer-btn-wrap .btn-file')[0].getBoundingClientRect()
                    pos = (x: 0, y: 0)
                    gwidth = Math.abs($('#file_drop').width())
                    gheight = Math.abs($('#file_drop').height())
                    pos.x = Math.round((btn_rect.left + btn_rect.right - gwidth) / 2)
                    pos.y = btn_rect.top - gheight - 4
                    $('#file_drop').css('left', pos.x).css('top', pos.y)
                    $('#file_drop').show()
                return false
            )

            $('body').dndhover().on('dndHoverEnd', (e) ->
                e.stopPropagation()
                e.preventDefault()
                $('#file_drop .drop-box').removeClass("dragover")
                $('#file_drop').hide()
                return false
            )

            $('body').on('dragover', (e) ->
                e.originalEvent.dataTransfer.dropEffect = 'copy'
                return false
            )

            $('body').on('drop', (e) ->
                e.stopPropagation()
                e.preventDefault()
                if($('#file_drop').has(e.target).length != 0)
                    filelist = e.originalEvent.dataTransfer.files
                    files = []
                    for i in [0...filelist.length]
                        files.push(filelist[i])
                    $scope.onUploadFiles(files)
                
                $('#file_drop .drop-box').removeClass("dragover")
                $('#file_drop').hide()
                return false
            )

            $('#file_drop .drop-box').on('dragover', (e) ->
                $(this).addClass("dragover")
                return false
            )

            $('#file_drop .drop-box').on('dragleave', (e) ->
                $(this).removeClass("dragover")
                return false
            )

            $('#file_drop').appendTo($('body'))

        )

        $scope.$on( "$locationChangeStart", ->
        )
        
        # check if there is sending message
        stopWatchingLocation = null
        handleLocationChangeStartEvent = (event) ->
            targetPath = $location.path()
            targetSearch = $location.search()
            targetHash = $location.hash()
            sending_count = 0
            if $scope.messages
                for msg in $scope.messages
                    if msg.cmsg_id < 0
                        sending_count += 1
            if sending_count > 0 || $scope.files && $scope.files.length > 0
                event.preventDefault()
                $dialogs.confirm('未送信のデータ', "未送信のデータが存在します。画面を遷移すると送信がキャンセルされます。よろしいでしょうか？", 'OK', ()->
                    $location.path(targetPath).search(targetSearch).hash(targetHash)
                    stopWatchingLocation()
                    $scope.$applyAsync(startWatchingForLocationChanges)           
                )
        startWatchingForLocationChanges = ->
            stopWatchingLocation = $scope.$on("$locationChangeStart", handleLocationChangeStartEvent)
        $timeout( startWatchingForLocationChanges, 0, false )


        angular.element('#input_bar .footer-btn-wrap .btn-smile').on('click', (e) ->
            e.preventDefault()
            ele = $(this)
            isShowing = ele.data('isShowing')
            ele.removeData('isShowing')
            if (isShowing != 'true')
                ele.data('isShowing', "true")
                btn_rect = ele[0].getBoundingClientRect()
                pos = (x: 0, y: 0)
                gwidth = Math.abs($('#emoticon_gallery').width())
                gheight = Math.abs($('#emoticon_gallery').height())
                pos.x = Math.round((btn_rect.left + btn_rect.right - gwidth) / 2)
                pos.y = btn_rect.top - gheight - 4
                $('#emoticon_gallery').css('left', pos.x).css('top', pos.y)
                $('#emoticon_gallery').show()
            else
                ele.data('isShowing', "false")
                $('#emoticon_gallery').hide()            
        )

        $scope.add_emoticon = (emo_text) ->
            txta = $('.item-input-wrapper textarea')
            start = txta.prop("selectionStart")
            str = ""
            if $api.is_empty($scope.cmsg.content)
                str = emo_text
                start = str.length
            else
                strPrefix = $scope.cmsg.content.substring(0, start)
                strSuffix = $scope.cmsg.content.substring(start)
                start += emo_text.length
                str = strPrefix + emo_text + strSuffix
            $scope.cmsg.content = str
            angular.element('#input_bar .footer-btn-wrap .btn-smile').data('isShowing', "false")
            angular.element('#emoticon_gallery').hide()

            $timeout(->
                chat_ta = document.getElementById('chat_ta')
                chat_ta.focus()
                chat_ta.setSelectionRange(start, start)
            )
            return

        angular.element('#input_bar .footer-btn-wrap .btn-to').on('click', (e) ->
            e.preventDefault()
            ele = $(this)
            isShowing = ele.data('isShowing')
            ele.removeData('isShowing')
            if (isShowing != 'true')
                ele.data('isShowing', "true")
                btn_rect = ele[0].getBoundingClientRect()
                pos = (x: 0, y: 0)
                gwidth = Math.abs($('#to_users').width())
                gheight = Math.abs($('#to_users').height())
                pos.x = Math.round((btn_rect.left + btn_rect.right - gwidth) / 2)
                pos.y = btn_rect.top - gheight - 4
                $('#to_users').css('left', pos.x).css('top', pos.y)
                $('#to_users').show()
            else
                ele.data('isShowing', "false")
                $('#to_users').hide()            
        )

        $scope.to_message = (member) ->
            txta = $('.item-input-wrapper textarea')
            start = txta.prop("selectionStart")
            str = ""
            if member == undefined
                to_text = "[to:all]全員\n"
            else
                to_text = "[to:" + member.user_id + "]" + $filter('sir_label')(member.user_name) + "\n"
            if $api.is_empty($scope.cmsg.content)
                str = to_text
                start = str.length
            else
                strPrefix = $scope.cmsg.content.substring(0, start)
                strSuffix = $scope.cmsg.content.substring(start)
                start += to_text.length
                str = strPrefix + to_text + strSuffix
            $scope.cmsg.content = str
            angular.element('#input_bar .footer-btn-wrap .btn-to').data('isShowing', "false")
            angular.element('#to_users').hide()

            $timeout(->
                chat_ta = document.getElementById('chat_ta')
                chat_ta.focus()
                chat_ta.setSelectionRange(start, start)
            )
            return

        # Upload files
        $scope.onUploadFiles = (files) ->
            if $api.is_empty($scope.files) || $scope.files.length == 0
                $scope.files = files
            else
                $scope.files = $scope.files.concat(files)
                
            ta = angular.element('#chat_ta')            
            $scope.$emit 'elastic:resize', ta

            angular.forEach files, (file) ->
                file.progress = 0
                size = Math.round(file.size  * 100 / (1024 * 1024)) / 100
                file.fileSize = size

                upload = chatStorage.upload_file($scope.mission_id, file).progress( (evt) ->
                    file.progress = parseInt(100.0 * evt.loaded / evt.total)
                ).success( (data, status, headers, config) ->
                    i = $scope.files.indexOf(file)
                    $scope.files.splice(i, 1)
                    $scope.$emit 'elastic:resize', ta                    
                    if data.err_code == 0
                        str = "[file id=" + data.mission_attach_id + " url='" + data.mission_attach_url + "']" + file.name + "[/file]"
                        if $rootScope.cur_mission && $rootScope.cur_mission.private_flag == 3 
                            to_id = $session.user_id
                        else
                            to_id = null
                        $chat.send(null, $rootScope.cur_home.home_id, $scope.mission_id, str, to_id, 1)
                    else
                        logger.logError(data.err_msg)
                )
                file.upload = upload
                return

        $scope.onCancelUpload = (file) ->
            ta = angular.element('#chat_ta')
            if file.upload
                chatStorage.cancel_upload_file(file)
                i = $scope.files.indexOf(file)
                $scope.files.splice(i, 1)
                $scope.$emit 'elastic:resize', ta                    

            return

        $scope.scrollTimer = null

        $scope.startScrollTimer = (cmsg_id, type) ->
            if $scope.scrollTimer != null
                $scope.stopScrollTimer()                
            $scope.scrollTimer = $timeout(->
                    scrollView = angular.element('#chat_view')
                    elem = angular.element('#chat_' + cmsg_id)
                    if(elem && elem[0])
                        rect = elem[0].getBoundingClientRect()
                        if(type == "prev")
                            scrollOffset = 0
                        else if(type == "next")
                            scrollOffset = scrollView.outerHeight()
                            if(rect)
                                scrollOffset -= rect.height
                        else
                            scrollOffset = 0
                        scrollView.duScrollToElement(angular.element('#chat_' + cmsg_id), scrollOffset)
                        $('#chat_view').removeClass('transparent')

                    $scope.stopScrollTimer()
                )

        $scope.stopScrollTimer = () ->
            if $scope.scrollTimer != null
                $timeout.cancel($scope.scrollTimer)
                $scope.scrollTimer = null

        $scope.prev = () ->
            if $scope.messages
                prev_id = $scope.messages[0].cmsg_id

                chatStorage.messages($scope.mission_id, prev_id)
                    .then((messages) ->
                        if messages.length > 0
                            $scope.messages[0].date_label = $dateutil.ellipsis_time_str($scope.messages[0].date, messages[0].date)
                            for i in [0..messages.length - 1]
                                $scope.messages.splice(i, 0, messages[i])
                            
                            ###
                            if $scope.messages.length > MAX_MSG_LENGTH
                                $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length-MAX_MSG_LENGTH)
                            ###
                            # build html
                            messages_html = chatStorage.messages_to_html(messages, $scope.chat_id)

                            el = $(messages_html)
                            $compile(el.contents())($scope)
                            $('#messages').prepend(el)

                            $scope.startScrollTimer(prev_id, "prev")

                            $scope.initEventHandler()

                            return messages
                        else
                            return []
                    )

        $scope.getMaxCid = ->
            max_id = 0
            if $api.is_empty($scope.messages)
                return max_id
            for message in $scope.messages
                if message.cmsg_id > max_id
                    max_id = message.cmsg_id
            return max_id

        $scope.next = () ->
            if $scope.messages
                length = $scope.messages.length
                if length == 0
                    return null
                    
                next_id = $scope.messages[length-1].cmsg_id

                if next_id < 0
                    return null

                chatStorage.messages($scope.mission_id, null, next_id)
                    .then((messages) ->
                        if messages.length > 0
                            max_cid = $scope.getMaxCid()
                            messages[0].date_label = $dateutil.ellipsis_time_str(messages[0].date, $scope.messages[length-1].date)
                            for i in [0..messages.length - 1]
                                if max_cid < messages[i].cmsg_id
                                    $scope.messages.push(messages[i])
                            
                            ###
                            if $scope.messages.length > MAX_MSG_LENGTH
                                $scope.messages.splice(0, $scope.messages.length-MAX_MSG_LENGTH)
                            ###
                            # build html
                            messages_html = chatStorage.messages_to_html(messages, $scope.chat_id)

                            el = $(messages_html)
                            $compile(el.contents())($scope)
                            $('#messages').append(el)

                            $scope.startScrollTimer(next_id, "next")
                            console.log("next_id:" + next_id)

                            $scope.initEventHandler()

                            return messages
                        else
                            return []
                    )

        $scope.$on("synced-server", ->
            $scope.sync()

            $scope.refreshBackImage()
        )

        # Search tasks by mission
        $scope.$on('select-mission', (event) ->
            taskStorage.refresh_remaining()
            $scope.refreshBackImage()
        )
 
        $scope.isMessageExist = (cmsg_id) ->
            exist = false
            for message in $scope.messages
                if message.cmsg_id == cmsg_id
                    exist = true
                    break
            return exist

        $scope.scrollToMessage = (cmsg_id, callback) ->
            if $scope.isMessageExist(cmsg_id)
                $scope.loaded_messages = true
                $scope.startScrollTimer(cmsg_id)
                if callback
                    callback()                
            else            
                length = $scope.messages.length
                if length > 0
                    f_msg = $scope.messages[0]
                    l_msg = $scope.messages[length-1]

                    if f_msg.cmsg_id > cmsg_id
                        $scope.prev()
                            .then((messages) ->
                                if $scope.isMessageExist(cmsg_id)
                                    $scope.loaded_messages = true
                                    $scope.startScrollTimer(cmsg_id)
                                    if callback
                                        callback()
                                else
                                    $scope.stopScrollTimer()
                                    $scope.scrollToMessage(cmsg_id, callback)
                            )
                    else if l_msg.cmsg_id < cmsg_id
                        res = $scope.next()
                        if res != null
                            res.then((messages) ->
                                if $api.is_empty(messages) || messages.length == 0
                                    if callback
                                        callback()
                                    return

                                if $scope.isMessageExist(cmsg_id)
                                    $scope.loaded_messages = true
                                    $scope.startScrollTimer(cmsg_id)
                                    if callback
                                        callback()                                    
                                else
                                    $scope.scrollToMessage(cmsg_id, callback)
                            )
                        else
                            if callback
                                callback()
            return

        $scope.$on('scroll-to-message', (event, cmsg_id) ->
            $scope.scrollToMessage(cmsg_id)
        )

        $scope.showTasks = ->
            if !$scope.show_tasks
                $scope.show_tasks = true
                $scope.show_process = false
                $scope.show_attach = false
                $scope.show_star = false
                $scope.show_summary = false
            else
                $scope.show_tasks = false
                $scope.max_right_panel = false

            $scope.onResize(true)
            return

        $scope.showProcess = ->
            if !$scope.show_process
                $scope.show_tasks = false
                $scope.show_process = true
                $scope.show_attach = false
                $scope.show_star = false
                $scope.show_summary = false
            else
                $scope.show_process = false
                $scope.max_right_panel = false

            $scope.onResize(true)
            $scope.focusInput()
            return

        $scope.showFiles = ->
            if !$scope.show_attach
                $scope.show_tasks = false
                $scope.show_process = false
                $scope.show_attach = true
                $scope.show_star = false
                $scope.show_summary = false
            else
                $scope.show_attach = false
                $scope.max_right_panel = false

            $scope.onResize(true)
            $scope.focusInput()
            return

        $scope.showStar = ->
            if !$scope.show_star
                $scope.show_tasks = false
                $scope.show_process = false
                $scope.show_attach = false
                $scope.show_star = true
                $scope.show_summary = false
            else
                $scope.show_star = false
                $scope.max_right_panel = false

            $scope.onResize(true)
            $scope.focusInput()
            return

        $scope.showSummary = ->
            if !$scope.show_summary
                $scope.show_tasks = false
                $scope.show_process = false
                $scope.show_attach = false
                $scope.show_star = false
                $scope.show_summary = true
            else
                $scope.show_summary = false
                $scope.max_right_panel = false

            $scope.onResize(true)
            $scope.focusInput()
            return

        $scope.render_message = (message) ->
            console.log("set message temp:" + message.temp_cmsg_id + " id:" + message.cmsg_id + " message:" + message.content)
            if (message.cmsg_id > 0 && message.temp_cmsg_id < 0)
                $('#chat_' + message.temp_cmsg_id).remove()

            # build html
            if ($('#chat_' + message.cmsg_id).length > 0)
                message_html = chatStorage.message_to_html(message, $scope.chat_id, false)
                $('#chat_' + message.cmsg_id).html(message_html)
                $compile($('#chat_' + message.cmsg_id).contents())($scope)
            else
                message_html = chatStorage.message_to_html(message, $scope.chat_id)
                el = $(message_html)
                $compile(el.contents())($scope)
                $('#messages').append(el)

        $scope.sendMessage = ->
            if $api.is_empty($scope.cmsg.content)
                return
                
            if $rootScope.cur_mission && $rootScope.cur_mission.private_flag == 3 
                to_id = $session.user_id
            else
                to_id = null

            length = $scope.messages.length
            if length > 1
                l_msg = $scope.messages[length-1]
                if $scope.last_cid != l_msg.cmsg_id # scrolled top and bottom messages was cutted
                    $scope.scrollToMessage($scope.last_cid, -> 
                        chatStorage.set_message($scope.mission_id, $scope.messages, $scope.cmsg, $scope.render_message)
                    ) # scroll to last cid
                else
                    chatStorage.set_message($scope.mission_id, $scope.messages, $scope.cmsg, $scope.render_message)
                    $scope.scrollToBottom()

            $chat.send($scope.cmsg.cmsg_id, $rootScope.cur_home.home_id, $scope.mission_id, $scope.cmsg.content, to_id)

            $scope.clear_cmsg(true)

        $scope.$on('receive_message', (event, cmsg) ->
            if cmsg.mission_id == $scope.mission_id
                console.log("receive cmsg_id:" + cmsg.cmsg_id)
                chatStorage.set_message($scope.mission_id, $scope.messages, cmsg, $scope.render_message)
                length = $scope.messages.length
                if length > 1
                    if cmsg.inserted
                        l_msg = $scope.messages[length-2]
                        if $scope.last_cid != l_msg.cmsg_id # scrolled top and bottom messages was cutted
                            $scope.messages.splice(length-1, 1) #remove new message
                            $scope.scrollToMessage(cmsg.cmsg_id) # scroll to new message
                            $scope.last_cid = cmsg.cmsg_id
                        else
                            $scope.last_cid = cmsg.cmsg_id
                            cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, l_msg.date)
                            $scope.scrollToBottom()
                    else if cmsg.cmsg_id > $scope.last_cid # in the case of replace temp id to correct id
                            $scope.last_cid = cmsg.cmsg_id
                else if length == 1
                    $scope.last_cid = cmsg.cmsg_id

                $scope.$apply()

                $scope.initEventHandler()
                return
        )

        $scope.initEventHandler = ->
            $('.preview-image').off('click')
            $('.preview-image').on('click', ->
                url = $(this).attr('preview-image')
                $dialogs.previewImage(url)
            )
            $('.preview-video').off('click')
            $('.preview-video').on('click', ->
                url = $(this).attr('preview-video')
                $dialogs.previewVideo(url)
            )

        $scope.scrollToBottom = (animate) ->
            $timeout(->
                try 
                    scrollView = angular.element('#chat_view')
                    if scrollView
                        if animate == false
                            scrollView.scrollToElement(angular.element('#scroll_bottom'))

                            $('#chat_view').removeClass('transparent')
                        else
                            scrollView.scrollToElementAnimated(angular.element('#scroll_bottom'))
                catch err
            , 1000)

        $scope.quote = (cmsg_id) ->
            if cmsg_id != undefined
                message = $scope.get_message(cmsg_id)
                return if message == null
            if message == undefined
                text = window.getSelection().toString()
                $('#selection_menu').hide()
                time = Math.floor(new Date().getTime() / 1000)
                str = "[引用 time=" + time + "]" + text + "[/引用 time=" + time + "]"
            else
                text = message.content
                time = new Date(message.date).getTime() / 1000
                str = "[引用 id=" + message.user_id + " name='" + message.user_name + "' time=" + time + "]" + text + "[/引用 time=" + time + "]"

            start = $('.item-input-wrapper textarea').prop("selectionStart")
            if !$api.is_empty($scope.cmsg.content)
                strPrefix = $scope.cmsg.content.substring(0, start)
                strSuffix = $scope.cmsg.content.substring(start)
                start += str.length
                str = strPrefix + str + strSuffix
            else
                start = str.length
            $scope.cmsg.content = str + "\n"
            $timeout(->
                chat_ta = document.getElementById('chat_ta')
                chat_ta.focus()
                chat_ta.setSelectionRange(start + 1, start + 1)
            )
            return

        $scope.link = (cmsg_id) ->
            message = $scope.get_message(cmsg_id)
            return if message == null
            start = $('.item-input-wrapper textarea').prop("selectionStart")
            time = new Date(message.date).getTime() / 1000
            str = "[link href='" + $scope.mission_id + "/" + message.cmsg_id + "'][/link]"
            if !$api.is_empty($scope.cmsg.content)
                strPrefix = $scope.cmsg.content.substring(0, start)
                strSuffix = $scope.cmsg.content.substring(start)
                start += str.length
                str = strPrefix + str + strSuffix
            else
                start = str.length
            $scope.cmsg.content = str
            $timeout(->
                chat_ta = document.getElementById('chat_ta')
                chat_ta.focus()
                chat_ta.setSelectionRange(start, start)
            )
            return

        $scope.edit = (cmsg_id) ->
            message = $scope.get_message(cmsg_id)
            return if message == null
            $scope.cmsg.editing = false
            $scope.cmsg.cmsg_id = message.cmsg_id
            $scope.cmsg.content = message.content

            message.editing = true

            $timeout(->
                angular.element('#chat_ta').focus()
            )
            return

        $scope.exitEdit = () ->
            $scope.cmsg.editing = false

            $scope.clear_cmsg(true)

        $scope.remove = (cmsg_id) ->
            message = $scope.get_message(cmsg_id)
            return if message == null
            $dialogs.confirm('メッセージ削除', "このメッセージを削除してもよろしいでしょうか？", '削除', ()->
                $chat.remove(message.cmsg_id, $scope.mission_id)
            )

        $scope.$on('remove_message', (event, cmsg) ->
            if cmsg.mission_id == $scope.mission_id
                chatStorage.remove_message($scope.messages, cmsg)
                if cmsg.unread
                    $rootScope.cur_mission.unreads--
                    homeStorage.set_unreads(-1)
                    chatStorage.refresh_unreads_title()
                    $rootScope.$broadcast('unread-message', $rootScope.cur_mission)

                if cmsg.cmsg_id == $scope.last_cid
                    length = $scope.messages.length
                    if length > 0
                        $scope.last_cid = $scope.messages[length-1].cmsg_id
                    else
                        $scope.last_cid = null

                $('#chat_' + cmsg.cmsg_id).remove();  
                $scope.$apply()
        )

        $scope.star = (cmsg_id) ->
            message = $scope.get_message(cmsg_id)
            return if message == null
            if message.star
                message.star = false
            else
                message.star = true

            $scope.render_message(message)

            chatStorage.star_message(message.cmsg_id, message.star)

        $rootScope.$on('unstar-message', (evt, message) ->
            for msg in $scope.messages
                if msg.cmsg_id == message.cmsg_id
                    msg.star = false
        )

        $scope.$on('elastic:resize', (event, ta)->
            chat_view = angular.element('#chat_view')
            curScrollHeight = chat_view[0].scrollHeight - chat_view.outerHeight()
            curScrollTop = chat_view.scrollTop()

            mustScrollToBottom = $scope.loaded_messages && (curScrollTop >= curScrollHeight)

            h = parseInt(ta[0].style.height, 10)
            if h < 34
                h = 34
            angular.element('#input_bar').height(h + "px")
            if($scope.files)
                fh = $scope.files.length * 25
            else
                fh = 0

            fileBar = angular.element('#file_bar');
            if $rootScope.canChat()
                if(fh > 0)
                    fileBar.css('bottom', h + 10 + "px")
                    chat_view.css('bottom', h + 10 + fh + 10 + "px")
                else
                    chat_view.css('bottom', h + 10 + "px")

            if mustScrollToBottom
                $scope.scrollToBottom()
        )

        $scope.onScroll = () ->
            $scope.startReadTimer(read_timer)

        $scope.readTimer = null

        $scope.startReadTimer = (duration) ->
            if $scope.readTimer != null
                $scope.stopReadTimer()                
            $scope.readTimer = $timeout(->
                    $scope.readInScroll()
                , duration)

        $scope.stopReadTimer = () ->
            if $scope.readTimer != null
                $timeout.cancel($scope.readTimer)
                $scope.readTimer = null

        $scope.onChangeWindowState = (visible) ->
            if visible == "visible"
                $scope.startReadTimer(read_timer)
            else
                $scope.stopReadTimer()

        $rootScope.$on('change-window-state', (evt, visible) ->
            $scope.onChangeWindowState(visible)
        )

        $scope.readInScroll = () ->            
            messages = $scope.messages
            parent = angular.element('#chat_view')[0]
            if parent                
                parentRect = angular.element('#chat_view')[0].getBoundingClientRect()

                readIds = []
                if messages
                    for message in messages
                        offset = angular.element('#chat_' + message.cmsg_id).offset()
                        if offset != undefined
                            messageTop = offset.top
                            if messageTop >= parentRect.top && messageTop < parentRect.bottom && message.unread
                                readIds.push(message.cmsg_id)

                if(readIds.length > 0)
                    chatStorage.read_messages($scope.mission_id, readIds)
                        .then((data) ->
                            if data.err_code == 0
                                delta = 0
                                for message in messages
                                    if readIds.indexOf(message.cmsg_id) != -1
                                        message.unread = false
                                        message.read_class = "unread read"
                                        if $('#chat_' + message.cmsg_id).length > 0
                                            $('#chat_' + message.cmsg_id + ' .unread-mark i').addClass(message.read_class)
                                        $rootScope.cur_mission.unreads--
                                        $rootScope.$broadcast('unread-message', $rootScope.cur_mission)
                                        if $rootScope.cur_mission.private_flag == 3
                                            $rootScope.bot_mission.unreads--
                                        delta--

                                mission = missionStorage.get_mission($rootScope.cur_mission.mission_id)
                                if $rootScope.cur_mission != mission
                                    missionStorage.set_mission($rootScope.cur_mission)
                                chatStorage.refresh_unreads_title()
                            else
                                logger.logError(data.err_msg)
                        )

        $scope.editMission = ->
            missionStorage.get($scope.mission_id, (res) ->
                if (res.err_code == 0)
                    $dialogs.editMission(res.mission)
                else
                    logger.logError(res.err_msg)
            )

        $scope.memberMission = ->
            missionStorage.get($scope.mission_id, (res) ->
                if (res.err_code == 0)
                    $dialogs.memberMission(res.mission)
                else
                    logger.logError(res.err_msg)
            )

        $scope.inviteMission = ->
            $dialogs.addMissionMember($rootScope.cur_mission)
            return

        # Resize
        if $rootScope.sideWidth == undefined
            $rootScope.sideWidth = 470
        $scope.handleWidth = 8

        angular.element(".resize-handle-h").draggable(
            axis: 'x'
            start: ->
                $(this).addClass('dragging')

            drag: (event, obj)->
                $scope.onResizeH(obj, false)
                
            stop: (event, obj)->
                $(this).removeClass('dragging')
                $scope.onResizeH(obj, true)
        )

        $scope.onResizeH = (obj, resize_handle) ->
            navHomeWidth = angular.element("#nav-home-container").width()
            navWidth = angular.element("#nav-container").width() + navHomeWidth
            x = obj.position.left

            if x < 400 
                if x > 300
                    $scope.max_right_panel = true
                    $scope.onResize(true)
                    return
                else 
                    $scope.max_right_panel = false
                    $scope.onResize(true)
                    return
            else if x > $window.innerWidth - navWidth - 450
                x = $window.innerWidth - navWidth - 450
                obj.position.left = x
            $rootScope.sideWidth = $window.innerWidth - navWidth - $scope.handleWidth - x

            $scope.onResize(resize_handle)

        # Resize
        $scope.onResize = (repos_handle) ->
            if $scope.show_tasks == false && $scope.show_process == false && $scope.show_attach == false && $scope.show_star == false && $scope.show_summary == false
                angular.element(".resize-handle-h").hide()
                angular.element('.chat-panel').css(
                    width: '100%'
                ).show()
                angular.element(".right-panel").hide()
            else
                angular.element(".resize-handle-h").show()

                if $scope.max_right_panel == false
                    if $rootScope.sideWidth != null
                        navHomeWidth = angular.element("#nav-home-container").width()
                        navWidth = angular.element("#nav-container").width() + navHomeWidth
                        mainWidth = $window.innerWidth - $rootScope.sideWidth - $scope.handleWidth - navWidth
                        angular.element('.chat-panel').css(
                            width: mainWidth + 'px'
                        ).show()
                        angular.element('.right-panel').css(
                            width: $rootScope.sideWidth + 'px'
                            left: mainWidth + $scope.handleWidth + 'px'
                        ).show()

                    if repos_handle 
                        angular.element(".resize-handle-h").css(
                            left: angular.element(".chat-panel").width() + 'px'
                        )
                else
                    navHomeWidth = angular.element("#nav-home-container").width()
                    navWidth = angular.element("#nav-container").width() + navHomeWidth
                    sideWidth = $window.innerWidth - $scope.handleWidth - navWidth   
                    angular.element('.chat-panel').hide()
                    angular.element('.right-panel').css(
                        width: sideWidth + 'px'
                        left: $scope.handleWidth + 'px'
                    ).show()

                    if repos_handle 
                        angular.element(".resize-handle-h").css(
                            width: $scope.handleWidth + 'px'
                            left: 0 + 'px'
                        )                
        
        $scope.onResize(true)

        $scope.$on('resize-window', ->
            $scope.onResize(true)
        )

        angular.element($window).bind('resize', ->
            $rootScope.$broadcast('resize-window')
        )

        $scope.maxRightPanel = () ->
            $scope.max_right_panel = !$scope.max_right_panel

            $scope.onResize(true)
            return

        # タスク登録
        $scope.addTask = (cmsg_id) ->
            if cmsg_id == undefined
                text = window.getSelection().toString()
                $('#selection_menu').hide()
            else
                message = $scope.get_message(cmsg_id)
                if message
                    text = message.content
            $dialogs.addTask($rootScope.cur_mission, text)

        # show user profile
        $scope.showUserProfile = (user_id) ->
            $dialogs.showUserProfile(user_id)
            return

        $scope.$on('refreshed-missions', (event) ->
            $scope.sync(false)
        )

        # チャットルームの管理
        $scope.canComplete = ->
            return $rootScope.cur_mission && $rootScope.canEditMission() && $rootScope.cur_mission.complete_flag == false && $rootScope.cur_mission.private_flag != 3

        $scope.canUncomplete = ->
            return $rootScope.cur_mission && $rootScope.canEditMission() && $rootScope.cur_mission.complete_flag != false && $rootScope.cur_mission.private_flag != 3

        $scope.canBreak = ->
            return $rootScope.cur_mission && !($session.user_id == $rootScope.cur_mission.client_id) && $rootScope.cur_mission.private_flag == 1

        # Complete mission
        $scope.completeMissionConfirm = ->
            message = $rootScope.cur_mission.mission_name + "をアーカイブしてもよろしいでしょうか？"
            $dialogs.confirm('チャットルーム', message, 'アーカイブ', () ->
                missionStorage.complete($rootScope.cur_mission.mission_id, 1, (data) ->
                    if data.err_code == 0
                        $rootScope.cur_mission = null
                        logger.logSuccess('チャットルームがアーカイブされました。')
                        $location.path('/home')

                        $(".mission_complete").removeClass('mission_complete_show').show()
                        $timeout( -> 
                            $(".mission_complete").addClass('mission_complete_show')
                            $timeout( -> 
                                $(".mission_complete").hide()
                                $rootScope.$broadcast('refresh-missions')
                                $rootScope.$broadcast('refresh-tasks')
                                $rootScope.$broadcast('refresh_back_image')
                            , 1500)
                        , 10)
                    else
                        logger.logError(data.err_msg)
                )
                return
            )

        # Unomplete mission
        $scope.uncompleteMissionConfirm = ->
            message = $rootScope.cur_mission.mission_name + "をアンアーカイブしてもよろしいでしょうか？"
            $dialogs.confirm('チャットルーム', message, 'アンアーカイブ', () ->
                missionStorage.complete($rootScope.cur_mission.mission_id, 0, (data) ->
                    if data.err_code == 0
                        $rootScope.$broadcast('refresh-missions')
                        $rootScope.$broadcast('refresh-tasks')
                        $rootScope.$broadcast('refresh_back_image')
                        logger.logSuccess('チャットルームがアンアーカイブされました。')
                    else
                        logger.logError(data.err_msg)
                )
                return
            )

        # Break from mission
        $scope.breakMissionConfirm = ->
            message = $rootScope.cur_mission.mission_name + "から退室します。よろしいでしょうか？"
            $dialogs.confirm('チャットルームから退室', message, '退室', () ->
                $dialogs.confirm('チャットルームから退室', 'チャットルームから退会すると元に戻すことができなくなります。よろしいでしょうか？', 'OK', ->
                    missionStorage.break_mission($rootScope.cur_mission.mission_id, (data) ->
                        if data.err_code == 0
                            $rootScope.$broadcast('refresh-missions')
                            $rootScope.$broadcast('refresh-tasks')
                            logger.logSuccess('チャットルームから外れました。')
                            $location.path('/home')
                        else
                            logger.logError(data.err_msg)
                    )
                    return
                , null, 'btn-danger')
            )

        $scope.$on('$destroy', () ->
            angular.forEach $rootScope.missions, (mission) ->
                if mission.mission_id == $scope.mission_id && mission.unreads > 0         
                    chatStorage.read_messages($scope.mission_id)
                        .then((data) ->
                            if data.err_code == 0
                                $timeout(() ->
                                    mission.unreads = 0
                                    $rootScope.$broadcast('unread-message', mission)
                                    chatStorage.refresh_unreads_title()
                                , 1000)
                            else
                                logger.logError(data.err_msg)
                        )

                return
        )

        $scope.refreshBackImage = ->
            cover = ''
            if $rootScope.cur_mission && $rootScope.cur_mission.job_back_url != null
                if $rootScope.cur_mission.job_back_pos == 1
                    back_pos = " repeat"
                else if $rootScope.cur_mission.job_back_pos == 2
                    back_pos = " no-repeat center center"
                else if $rootScope.cur_mission.job_back_pos == 3
                    back_pos = " no-repeat left top"
                else
                    back_pos = " no-repeat center center"
                    cover = 'cover'
                $('.chat-view').css('background', 'url(' + encodeURI($rootScope.cur_mission.job_back_url) + ') ' + back_pos)
            else
                $('.chat-view').css('background', '')

            $('.chat-view').css('-webkit-background-size', cover)
            $('.chat-view').css('-moz-background-size', cover)
            $('.chat-view').css('-o-background-size', cover)
            $('.chat-view').css('background-size', cover)
            return

        $scope.$on('refresh_back_image', ->
            $scope.refreshBackImage()
        )

        $scope.refreshBackImage()

        # Initialize
        $scope.sync = (load_chat_message) ->
            $scope.session = $session
            $scope.mission_id = parseInt($routeParams.mission_id, 10)
            $scope.chat_id = parseInt($routeParams.chat_id, 10)

            $scope.load_messages(chatStorage.cache_messages($scope.mission_id))

            mission = missionStorage.get_mission($scope.mission_id)
            if $session.user_id != null 
                $rootScope.cur_mission = mission
                missionStorage.select_mission_in_nav()
                $scope.refreshBackImage()
                if $api.is_empty(mission) || ((mission.private_flag==0 || mission.private_flag==1) && $api.is_empty(mission.members))
                    missionStorage.get($scope.mission_id, (res) ->
                        if (res.err_code == 0) 
                            if $rootScope.cur_home.home_id != res.mission.home_id
                                home = homeStorage.get_home(res.mission.home_id)
                                if home == null
                                    $location.path('/home')
                                homeStorage.select(home, () ->
                                )
                                return

                            $rootScope.cur_mission = res.mission
                            $scope.refreshBackImage()
                            $rootScope.cur_mission.visible = true
                            missionStorage.select_mission_in_nav()
                            missionStorage.set_mission($rootScope.cur_mission)
                        else 
                            logger.logError(res.err_msg)
                            $location.path('/home')
                    )
                else if $rootScope.cur_home.home_id != mission.home_id
                    home = homeStorage.get_home(mission.home_id)
                    if home == null
                        $location.path('/home')
                    homeStorage.select(home, () ->
                    )
                    return
                else
                    $rootScope.cur_mission.visible = true

                if !$rootScope.canChat()
                    angular.element('#chat_view').css('bottom', "0px")

                if load_chat_message != false
                    $scope.init_cmsg()
                    chatStorage.messages($scope.mission_id)
                        .then((messages) ->
                            chatStorage.cache_messages($scope.mission_id, messages)
                            $scope.load_messages(messages)
                        )
        
        $scope.sync()

        return
)

.constant('chatInputConfig', {
    append: ''
})

.directive('chatInput', 
    ($timeout, $window, chatInputConfig) ->
        'use strict'
        {
            require: 'ngModel'
            restrict: 'A, C'
            link: (scope, element, attrs, ngModel) ->
                # cache a reference to the DOM element
                ta = element[0]
                $ta = element
                # ensure the element is a textarea, and browser is capable

                initMirror = ->
                    mirrorStyle = mirrorInitStyle
                    mirrored = ta
                    # copy the essential styles from the textarea to the mirror
                    taStyle = getComputedStyle(ta)
                    angular.forEach copyStyle, (val) ->
                        mirrorStyle += val + ':' + taStyle.getPropertyValue(val) + ';'
                        return
                    mirror.setAttribute 'style', mirrorStyle
                    return

                adjust = ->
                    taHeight = undefined
                    taComputedStyleWidth = undefined
                    mirrorHeight = undefined
                    width = undefined
                    overflow = undefined
                    if mirrored != ta
                        initMirror()
                    # active flag prevents actions in function from calling adjust again
                    if !active
                        active = true
                        mirror.value = ta.value + append
                        # optional whitespace to improve animation
                        mirror.style.overflowY = ta.style.overflowY
                        taHeight = if ta.style.height == '' then 'auto' else parseInt(ta.style.height, 10)
                        taComputedStyleWidth = getComputedStyle(ta).getPropertyValue('width')
                        # ensure getComputedStyle has returned a readable 'used value' pixel width
                        if taComputedStyleWidth.substr(taComputedStyleWidth.length - 2, 2) == 'px'
                            # update mirror width in case the textarea width has changed
                            width = parseInt(taComputedStyleWidth, 10) - (boxOuter.width)
                            mirror.style.width = width + 'px'
                        mirrorHeight = mirror.scrollHeight
                        if mirrorHeight > maxHeight
                            mirrorHeight = maxHeight
                            overflow = 'scroll'
                        else if mirrorHeight < minHeight
                            mirrorHeight = minHeight
                        mirrorHeight += boxOuter.height
                        if mirrorHeight < 24
                            mirrorHeight = 24
                        ta.style.overflowY = overflow or 'hidden'
                        if taHeight != mirrorHeight
                            ta.style.height = mirrorHeight + 'px'
                            scope.$emit 'elastic:resize', $ta
                        scope.$emit 'taResize', $ta
                        # listen to this in the UserMessagesCtrl
                        # small delay to prevent an infinite loop
                        $timeout (->
                            active = false
                            return
                        ), 1
                    return

                forceAdjust = ->
                    active = false
                    adjust()
                    return

                if ta.nodeName != 'TEXTAREA' or !$window.getComputedStyle
                    return
                # set these properties before measuring dimensions
                $ta.css
                    'overflow': 'hidden'
                    'overflow-y': 'hidden'
                    'word-wrap': 'break-word'
                    'max-height': '243px'
                # force text reflow
                text = ta.value
                ta.value = ''
                ta.value = text
                append = if attrs.chatInput then attrs.chatInput.replace(/\\n/g, '\n') else chatInputConfig.append
                $win = angular.element($window)
                mirrorInitStyle = 'position: absolute; top: -999px; right: auto; bottom: auto;' + 'left: 0; overflow: hidden; -webkit-box-sizing: content-box;' + '-moz-box-sizing: content-box; box-sizing: content-box;' + 'min-height: 0 !important; height: 0 !important; padding: 0;' + 'word-wrap: break-word; border: 0;'
                $mirror = angular.element('<textarea tabindex="-1" ' + 'style="' + mirrorInitStyle + '"/>').data('elastic', true)
                mirror = $mirror[0]
                taStyle = getComputedStyle(ta)
                resize = taStyle.getPropertyValue('resize')
                borderBox = taStyle.getPropertyValue('box-sizing') == 'border-box' or taStyle.getPropertyValue('-moz-box-sizing') == 'border-box' or taStyle.getPropertyValue('-webkit-box-sizing') == 'border-box'
                if !borderBox 
                    boxOuter = 
                        width: 0
                        height: 0 
                else
                    boxOuter = 
                        width: parseInt(taStyle.getPropertyValue('border-right-width'), 10) + parseInt(taStyle.getPropertyValue('padding-right'), 10) + parseInt(taStyle.getPropertyValue('padding-left'), 10) + parseInt(taStyle.getPropertyValue('border-left-width'), 10)
                        height: parseInt(taStyle.getPropertyValue('border-top-width'), 10) + parseInt(taStyle.getPropertyValue('padding-top'), 10) + parseInt(taStyle.getPropertyValue('padding-bottom'), 10) + parseInt(taStyle.getPropertyValue('border-bottom-width'), 10)
                minHeightValue = parseInt(taStyle.getPropertyValue('min-height'), 10)
                heightValue = parseInt(taStyle.getPropertyValue('height'), 10)
                minHeight = Math.max(minHeightValue, heightValue) - (boxOuter.height)
                maxHeight = parseInt(taStyle.getPropertyValue('max-height'), 10)
                mirrored = undefined
                active = undefined
                copyStyle = [
                    'font-family'
                    'font-size'
                    'font-weight'
                    'font-style'
                    'letter-spacing'
                    'line-height'
                    'text-transform'
                    'word-spacing'
                    'text-indent'
                ]
                # exit if elastic already applied (or is the mirror element)
                if $ta.data('elastic')
                    return
                # Opera returns max-height of -1 if not set
                maxHeight = if maxHeight and maxHeight > 0 then maxHeight else 9e4
                # append mirror to the DOM
                if mirror.parentNode != document.body
                    angular.element(document.body).append mirror
                # set resize and apply elastic
                $ta.css('resize': if resize == 'none' or resize == 'vertical' then 'none' else 'horizontal').data 'elastic', true

                ###
                # initialise
                ###

                # listen
                if 'onpropertychange' of ta and 'oninput' of ta
                    # IE9
                    ta['oninput'] = ta.onkeyup = adjust
                else
                    ta['oninput'] = adjust
                $win.bind 'resize', forceAdjust
                scope.$watch (->
                    ngModel.$modelValue
                ), (newValue) ->
                    forceAdjust()
                    return
                scope.$on 'elastic:adjust', ->
                    initMirror()
                    forceAdjust()
                    return
                $timeout adjust

                ###
                # destroy
                ###

                scope.$on '$destroy', ->
                    $mirror.remove()
                    $win.unbind 'resize', forceAdjust
                    return
                return

        }
)
.directive('messageList', 
    ($compile, chatStorage, $timeout, $rootScope) ->
        getTemplate = (scope) ->
            # build html
            return chatStorage.messages_to_html(scope.messages, scope.chat_id);

        linker = (scope, element, attrs) ->
            element.html(getTemplate(scope.$parent))
            $compile(element.contents())(scope)

            $timeout(->
                $('#loader').hide()
                $rootScope.$broadcast('elastic:adjust')
                if (scope.$parent)
                    scope.$parent.initEventHandler()
            , 1000)

        return {
            restrict: "E"
            replace: true
            link: linker
        }
)
