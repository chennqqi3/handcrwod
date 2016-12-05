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
    ($rootScope, $http, CONFIG, logger, $location, $timeout) ->
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

        this.signinParamsFromStorage = ->
            try 
                return JSON.parse(localStorage.getItem('signin_params') || {})
            catch err
                return {}

        this.signinParamsToStorage = (params)->
            try 
                localStorage.setItem('signin_params', JSON.stringify(params))                
            catch err
                return {}

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
            $rootScope.alerts = data.alerts
            $rootScope.unreads = data.unreads
            $rootScope.chat_uri = data.chat_uri
            $rootScope.cache_uris = data.cache_uris
            $rootScope.cache_uri = $rootScope.cache_uris[Math.floor(Math.random() * $rootScope.cache_uris.length)]

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
            $rootScope.homes = []
            $rootScope.cur_home = null
            $rootScope.missions = []
            $rootScope.cur_mission = null
            $rootScope.tasks = []
            $rootScope.alerts = []
            $rootScope.unreads = []

            try
                localStorage.setItem(SESSION, null)
            catch err

        this.getTOKEN = ->
            return '' if this.user_id == undefined || this.session_id == undefined || this.user_id == null || this.session_id == null
            return this.user_id + ":" + this.session_id

        return this
)

.factory('$auth', 
    ($api, $session, $rootScope, $location, $http, AUTH_EVENTS, CONFIG, logger, missionStorage, homeStorage) ->
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
                            $session.create(data.user)

                            homeStorage.set_cur_home(data.user.cur_home, false)
                            missionStorage.set_cur_mission((if states.cur_mission != null then states.cur_mission else null), false)

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
                        homeStorage.set_cur_home(res.data.cur_home, false)
                        missionStorage.set_cur_mission(res.data.cur_mission, false)
                    return res.data.err_code
                )

        authService.signup = (user, callback) ->
            return $api
                .call('user/signup', user)
                .then((res) ->
                    if res.data.err_code == 0
                        $session.create(res.data)
                        homeStorage.set_cur_home(res.data.cur_home, false)
                        missionStorage.set_cur_mission(res.data.cur_mission, false)
                    
                    if callback != undefined
                        callback(res.data)
                )

        authService.signupGoogle = (user, callback) ->
            return $api
                .call('google/register', user)
                .then((res) ->
                    if res.data.err_code == 0
                        $session.create(res.data)
                        homeStorage.set_cur_home(res.data.cur_home, false)
                        missionStorage.set_cur_mission(res.data.cur_mission, false)
                    
                    if callback != undefined
                        callback(res.data)
                )

        authService.signupFacebook = (user, callback) ->
            return $api
                .call('facebook/register', user)
                .then((res) ->
                    if res.data.err_code == 0
                        $session.create(res.data)
                        homeStorage.set_cur_home(res.data.cur_home, false)
                        missionStorage.set_cur_mission(res.data.cur_mission, false)
                    
                    if callback != undefined
                        callback(res.data)
                )
        
        authService.activate = (credentials) ->
            return $api
                .call('user/activate', credentials)
                .then((res) ->
                    if res.data.err_code == 0
                        $session.create(res.data)
                        homeStorage.set_cur_home(res.data.cur_home, false)
                        missionStorage.set_cur_mission(res.data.cur_mission, false)
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
                        homeStorage.set_cur_home(res.data.cur_home, false)
                        missionStorage.set_cur_mission(res.data.cur_mission, false)
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
                        homeStorage.set_cur_home(res.data.cur_home, false)
                        missionStorage.set_cur_mission(res.data.cur_mission, false)
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