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
    'app.dateutil'
    'app.storage'
    'app.auth'
    'app.dialogs'
    'app.service.chat'
    'app.consts'

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
    'app.home.open'
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
            check = false
            a = $window.navigator.userAgent || $window.navigator.vendor || $window.opera
            if /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))
                check = true
                
            return check

        $rootScope.$on('$routeChangeStart', (event, next, current) -> 
            mobileEnablePages = [
                '/signup'
                '/activate'
                '/forgotpwd'
                '/resetpwd'                
            ]

            if $window.mobilecheck()
                if mobileEnablePages.indexOf(next.originalPath) != -1
                    return
                $location.path("/loadapp")
                return

            if next.originalPath != undefined
                if next.originalPath == "/signin/:token?"
                    return

                if next.originalPath == "/signout"
                    $auth.logout()
                
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
                        return $location.absUrl()
                    )
                    return

                $timeout( -> 
                    init()
                , 10)
        }
)