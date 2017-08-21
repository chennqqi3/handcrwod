'use strict';

angular.module('app', [
    'app.config'

    # Angular modules
    'ngRoute'
    'ngAnimate'

    # 3rd Party Modules
    'ui.bootstrap'
    'angularFileUpload'
    'ngWebsocket'
    'duScroll'

    # Custom modules
    'app.logger'
    'app.controller.main'
    'app.controller.nav'
    'app.directives'
    'app.localization'
    
    # common
    'app.api'
    'app.cache'
    'app.dateutil'
    'app.storage'
    'app.auth'
    'app.dialogs'
    'app.service.chat'
    'app.consts'
    'app.qr'

    # storage
    'app.storage.home'
    'app.storage.mission'
    'app.storage.task'
    'app.storage.chat'
    'app.storage.user'

    # user session
    'app.signin'
    'app.signup'
    'app.signup_facebook'
    'app.signup_google'
    'app.signout'
    'app.activate'
    'app.forgotpwd'
    'app.resetpwd'

    'app.chatroom'
    'app.sel_mission'
    'app.sel_date'
    'app.sel_skill'
    'app.sel_performer'
    'app.sel_hours'
    'app.sel_budget'
    'app.settings'
    'app.sel_template'

    # home
    'app.home'
    'app.home.add'
    'app.home.invite'
    'app.home.edit'

    # bot
    'app.bot'

    # mission
    'app.mission.add'
    'app.mission.open'
    'app.mission.edit'
    'app.mission.member'
    'app.mission.member_add'
    'app.mission.invite'
    'app.mission.attach'
    'app.mission.emoticon'

    # task
    'app.task.list'
    'app.task.edit'
    'app.task.star'
    'app.task.process'

    # chat
    'app.chatroom.search'
    'app.chat.star'
])
.config(
    ($routeProvider, USER_ROLES, CONFIG) ->
        ver = "?v=" + CONFIG.VER
        $routeProvider
            .when(
                '/'
                redirectTo: '/home'
            )
            # Handcrowd
            .when(
                '/priority/:mission_id?'
                templateUrl: 'views/tasks.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/task/star'
                templateUrl: 'views/task/task_star.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/home/:home_id?'
                templateUrl: 'views/home/home.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/bot'
                templateUrl: 'views/home/bot.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/inbox/:mission_id?'
                templateUrl: 'views/tasks.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/missions/:mission_id?'
                templateUrl: 'views/tasks.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/chats/:mission_id/:chat_id?'
                templateUrl: 'views/chatroom/chatroom.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/process/:mission_id?'
                templateUrl: 'views/process/process.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/search'
                templateUrl: 'views/tasks.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            .when(
                '/settings'
                templateUrl: 'views/settings.html' + ver
                data:
                    authorizedRoles: [USER_ROLES.user]
            )
            # from qr code
            .when(
                '/qr/home/:home_id/:invite_key'
                templateUrl: 'views/qr/home.html' + ver
            )
            .when(
                '/qr/chat/:mission_id/:invite_key'
                templateUrl: 'views/qr/chat.html' + ver
            )
            .when(
                '/signin/:token?/:from?'
                templateUrl: 'views/account/signin.html' + ver
            )
            .when(
                '/signout'
                templateUrl: 'views/account/signout.html' + ver
            )
            .when(
                '/signup'
                templateUrl: 'views/account/signup.html' + ver
            )
            .when(
                '/signup_facebook/:token'
                templateUrl: 'views/account/signup_facebook.html' + ver
            )
            .when(
                '/signup_google/:token'
                templateUrl: 'views/account/signup_google.html' + ver
            )
            .when(
                '/activate'
                templateUrl: 'views/account/activate.html' + ver
            )
            .when(
                '/forgotpwd'
                templateUrl: 'views/account/forgotpwd.html' + ver
            )
            .when(
                '/resetpwd'
                templateUrl: 'views/account/resetpwd.html' + ver
            )
            .when(
                '/loadapp'
                templateUrl: 'views/account/loadapp.html' + ver
            )            
            .when(
                '/404'
                templateUrl: 'views/pages/404.html' + ver
            )

            .otherwise(
                redirectTo: '/404'
            )
)

.run(
    ($rootScope, AUTH_EVENTS, $auth, $chat, $location, $syncServer, 
        $route, $window, CONFIG, $dialogs, $api) ->
        $rootScope.err_required = "必須項目です。"
        $rootScope.err_invalid_zip_code = "3文字以上の半角数字を入力してください。ハイフンは含めないで下さい。"
        $rootScope.err_invalid_tel = "例：半角数字090-1234-5678"
        $rootScope.err_invalid_email = "例：tanaka@gmail.com"
        $rootScope.err_invalid_maxlength = "文字以下に入力してください。"
        $rootScope.err_invalid_pdf = "PDFファイルではありません。"
        $rootScope.err_invalid_date = "例：H9-01-01"
        $rootScope.err_small_end_date = "終了日付を開始日付以降に入力してください。"            
        $rootScope.err_no_data = "データーがありません。"
        $rootScope.err_no_equal_password = "同じパスワードを入力してください。"
        $rootScope.err_invalid_taking_period = "半角数字を入力して下さい。"
        $rootScope.err_invalid_furigana = "ひらがなを入力してください。"
        $rootScope.err_send_method = "送付方法を選択してください。"
        $rootScope.err_invalid_youtube = "有効なYoutube　URLではありません。"
        $rootScope.wait_responding = "データー取得中…"
            
        $rootScope.ver = "?v=" + CONFIG.VER
        $rootScope.error_disconnected = false

        $window.mobilecheck = ->
            return $window.isIOS() || $window.isAndroid()

        $window.isAndroid = ->
            check = false
            a = $window.navigator.userAgent || $window.navigator.vendor || $window.opera
            if /(android|bb\d+|meego).+mobile/i.test(a)
                check = true
                
            return check

        $window.isIOS = ->
            check = false
            a = $window.navigator.userAgent || $window.navigator.vendor || $window.opera
            if /ip(hone|od)/i.test(a)
                check = true
                
            return check

        $rootScope.$on('$routeChangeStart', (event, next, current) -> 
            mobileEnablePages = [
                '/signup'
                '/activate'
                '/forgotpwd'
                '/resetpwd'
                '/qr/home/:home_id/:invite_key'
                '/qr/chat/:mission_id/:invite_key'
                '/loadapp'
            ]

            if next.originalPath != undefined
                if $window.mobilecheck()
                    for p in mobileEnablePages
                        if p.indexOf(next.originalPath) != -1
                            return
                    $location.path("/loadapp")
                    return

                if next.originalPath == "/signin/:token?/:from?"
                    return

                if next.originalPath == "/signout"
                    $auth.logout()
                    return
                
            authorizedRoles = next.data.authorizedRoles if next.data
            return if !authorizedRoles

            if !$auth.isAuthorized(authorizedRoles)
                $auth.autoLogin(null, authorizedRoles, event)
        )

        $rootScope.$on('$routeChangeSuccess', (event, current, previous, rejection) ->
            console.log 'routeChangeSuccess ' + $location.path()
        )
        
        init = ->
            $syncServer.sync(false)

            if $rootScope.alerts.length > 0
                $dialogs.showAlerts()
            return

        $rootScope.$on(AUTH_EVENTS.loginSuccess, ->
            init()
        )

        $rootScope.$on('reload_session', ->
            init()
        )

        $api.init_notification()

        # check active window
        changeWindowState = (evt) ->
            v = "visible"
            h = "hidden"
            evtMap =
                focus: v
                focusin: v
                pageshow: v
                blur: h
                focusout: h
                pagehide: h

            evt = evt or window.event

            visible = ""

            if evt.type of evtMap
                visible = evtMap[evt.type]
            else
                visible = (if this[hidden] then "hidden" else "visible")

            $rootScope.windowState = visible
            $rootScope.$broadcast('change-window-state', visible)
            return
            
        hidden = "hidden"
        if hidden of document
            document.addEventListener "visibilitychange", changeWindowState
        else if (hidden = "mozHidden") of document
            document.addEventListener "mozvisibilitychange", changeWindowState
        else if (hidden = "webkitHidden") of document
            document.addEventListener "webkitvisibilitychange", changeWindowState
        else if (hidden = "msHidden") of document
            document.addEventListener "msvisibilitychange", changeWindowState
        else if "onfocusin" of document
            document.onfocusin = document.onfocusout = changeWindowState
        else
            window.onpageshow = window.onpagehide = window.onfocus = window.onblur = changeWindowState

         # set the initial state (but only if browser supports the Page Visibility API)
        if document[hidden] isnt `undefined`
            changeWindowState type: (if document[hidden] then "blur" else "focus")
        return
)

.service('$clip', 
    ($rootScope, logger, $window) ->
        $window.ZeroClipboard.config( { swfPath: "swf/ZeroClipboard.swf" } )

        this.createClient = (el, fnClipData) ->
            client = new $window.ZeroClipboard(el)

            client.on( 'ready', (event) ->
                console.log( 'zeroclipboard swf is loaded' )

                client.on( 'copy', (event) ->
                    textData = fnClipData()
                    event.clipboardData.setData('text/plain', textData)
                )

                client.on( 'aftercopy', (event) ->
                    console.log('Copied text to clipboard: ' + event.data['text/plain'])
                )
            )

            client.on( 'error', (event) ->
                console.log( 'ZeroClipboard error of type "' + event.name + '": ' + event.message )
                $window.ZeroClipboard.destroy()
            )

            return client

        return this
)

.directive('copyUrl', 
    ($timeout, $rootScope, $parse, $api, $location, $clip, logger) ->
        return {
            restrict: 'A'
            link: (scope, element, attrs, ngModel) ->
                init = ->
                    clipLink = $clip.createClient($(element), ->
                        logger.logSuccess("URLをコピーしました")
                        return $(element).attr('copy-url')
                    )
                    return

                $timeout( -> 
                    init()
                , 10)
        }
)