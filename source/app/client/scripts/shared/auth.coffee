angular.module('app.auth', [])

.constant('AUTH_EVENTS',
    loginSuccess: 'auth-login-success'
    loginFailed: 'auth-login-failed'
    logoutSuccess: 'auth-logout-success'
    sessionTimeout: 'auth-session-timeout'
    notAuthenticated: 'auth-not-authenticated'
    notAuthorized: 'auth-not-authorized'
)
.constant('USER_ROLES', 
    all: '*'
    user: 'user'
)
.service('$session', 
    ($rootScope, $http, CONFIG, logger, $location) ->
        SESSION = 'session'
        $this = this
        this.session_id = null
        this.user_id = null
        this.planconfig = null

        this.fromStorage = ->
            try 
                encoded = localStorage.getItem(SESSION)
                s = JSON.parse(sjcl.decrypt("hc2015", encoded) || {})
                if s.session_id == undefined
                    s.session_id = null
                if s.user_id == undefined
                    s.user_id = null
                if s.planconfig == undefined
                    s.planconfig = null
            catch err
                s = { session_id: null }
            return s

        this.statesFromStorage = ->
            try 
                return JSON.parse(localStorage.getItem('states') || {})
            catch err
                return {}

        this.statesToStorage = ->
            try
                localStorage.setItem('states', JSON.stringify({
                        lastPath: $rootScope.lastPath
                        cur_home: $rootScope.cur_home
                        cur_mission: $rootScope.cur_mission
                    }))
            catch err

        $rootScope.$on('select-mission', () ->
            $this.statesToStorage()
        )

        $rootScope.$on('refresh-task-date', () ->
            $this.statesToStorage()
        )

        $rootScope.$on('refresh-task-date', () ->
            $this.statesToStorage()
        )

        $rootScope.$on('search-task', () ->
            $this.statesToStorage()
        )

        $rootScope.$on('select-member', () ->
            $this.statesToStorage()
        )

        this.saveStates = (path) ->
            $rootScope.lastPath = path
            $this.statesToStorage()

        this.create = (data) ->
            this.session_id = data.session_id
            this.user_id = data.user_id
            this.user_role = 'user'
            this.user_name = data.user_name
            this.email = data.email
            this.avartar = data.avartar
            this.language = data.language
            this.time_zone = data.time_zone
            this.states = {}
            this.planconfig = data.plan
            $rootScope.cur_home = data.cur_home
            $rootScope.alerts = data.alerts
            $rootScope.chat_uri = data.chat_uri
            this.setCurHome(data.cur_home, false)
            this.setCurMission(data.cur_mission, false)
            this.statesToStorage()

            encoded = sjcl.encrypt("hc2015", JSON.stringify(
                session_id: this.session_id
                user_id: this.user_id
            ))

            try
                localStorage.setItem(SESSION, encoded)
            catch err

        this.destroy = ->
            this.session_id = null
            this.user_id = null
            this.user_role = null
            this.user_name = null
            this.email = null
            this.avartar = null
            this.language = null
            this.time_zone = null
            this.states = null
            this.planconfig = null

            try
                localStorage.setItem(SESSION, null)
            catch err

        this.getTOKEN = ->
            return '' if this.user_id == undefined || this.session_id == undefined || this.user_id == null || this.session_id == null
            return this.user_id + ":" + this.session_id

        this.setCurHome = (home, toStorage) ->
            if toStorage == undefined
                toStorage = true
            old_home_id = null
            new_home_id = null
            if $rootScope.cur_home == undefined
                $rootScope.cur_home = null

            if $rootScope.cur_home != null
                old_home_id = $rootScope.cur_home.home_id
            if home != null && home.home_id != null
                new_home_id = home.home_id
            $rootScope.cur_home = home

            if toStorage
                $this.statesToStorage()
            
            if old_home_id != new_home_id
                $rootScope.$broadcast('select-home')
            return

        this.setCurMission = (mission, toStorage) ->
            if toStorage == undefined
                toStorage = true
            $rootScope.cur_mission = mission
            if toStorage
                $this.statesToStorage()
            return

        return this
)

.factory('$auth', 
    ($api, $session, $rootScope, $location, $http, AUTH_EVENTS, CONFIG, logger) ->
        authService = {}

        authService.autoLogin = (session_id, authorizedRoles, event) ->            
            console.log("try auto login")
   
            session = $session.fromStorage()
            states = $session.statesFromStorage()
            if session_id == null
                session_id = session.session_id
            else if session_id != session.session_id
                states.cur_mission = null
                states.lastPath = null
                states.cur_home = null
            
            if session_id != null
                path = $location.path()
                token = session.user_id + ":" + session_id
                cur_home_id = if states.cur_home != null then states.cur_home.home_id else null
                $api.call('user/get_profile', { TOKEN: token, 'cur_home_id': cur_home_id })
                    .success((data, status, headers, config) ->
                        if data.err_code == 0
                            $session.create(
                                session_id: session_id
                                user_id: data.user.user_id
                                user_role: 'user'
                                user_name: data.user.user_name
                                email: data.user.email
                                avartar: data.user.avartar
                                language: data.user.language
                                time_zone: data.user.time_zone
                                plan: data.user.plan
                                cur_home: data.user.cur_home
                                cur_mission: if states.cur_mission != null then states.cur_mission else null
                                alerts: data.user.alerts
                                chat_uri: data.user.chat_uri
                            )

                            $rootScope.$broadcast('reload_session')
                            if path.indexOf('/signin') == 0
                                $location.path('/home')
                            else
                                $location.path(path)
                        else
                            authService.checkAuth(authorizedRoles, event)
                    )
                    .error((data, status, headers, config) ->
                        logger.logError('サーバーへ接続することができません。')
                        authService.checkAuth(authorizedRoles, event)
                    )
            else
                authService.checkAuth(authorizedRoles, event)

            return

        authService.checkAuth = (authorizedRoles, event) ->
            if !authService.isAuthorized(authorizedRoles)
                if event != null
                    event.preventDefault()
                $location.path("signin")

            if authService.isAuthenticated()
                # user is not allowed
                $rootScope.$broadcast(AUTH_EVENTS.notAuthorized)
            else
                # user is not logged in
                $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated)

        authService.login = (credentials) ->
            return $api
                .call('user/signin', credentials)
                .then((res) ->
                    if res.data.err_code == 0
                        $session.create(res.data)
                    return res.data.err_code
                )
        
        authService.activate = (credentials) ->
            return $api
                .call('user/activate', credentials)
                .then((res) ->
                    if res.data.err_code == 0
                        $session.create(res.data)
                    return res.data
                )

        authService.loginFacebook = (token) ->
            return $api
                .call('facebook/signin', {
                    token: token
                })
                .then((res) ->
                    if res.data.err_code == 0
                        $session.create(res.data)
                    return res.data.err_code
                )
        
        authService.loginGoogle = (token) ->
            return $api
                .call('google/signin', {
                    token: token
                })
                .then((res) ->
                    if res.data.err_code == 0
                        $session.create(res.data)
                    return res.data.err_code
                )

        authService.logout = ->
            return $api
                .call('user/signout')
                .then((res) ->
                    $session.destroy()
                    $rootScope.$broadcast('closed_session')
                )

        authService.isAuthenticated = ->
            return !!$session.user_id

        authService.isAuthorized = (authorizedRoles) ->
            if not angular.isArray(authorizedRoles)
                authorizedRoles = [authorizedRoles]
            
            return (authService.isAuthenticated() &&
              authorizedRoles.indexOf($session.user_role) != -1)

        return authService
)