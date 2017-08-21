'use strict';

angular.module('app.controller.main', [])

# overall control
.controller('AppCtrl', 
    ($scope, $location, $rootScope) ->
        $scope.isSpecificPage = ->
            path = $location.path()
            found = false
            for p in [
                '/404'
                '/pages/500'
                '/login'
                '/signin'
                '/signup'
                '/signup_facebook'
                '/signup_google'
                '/activate'
                '/forgotpwd'
                '/resetpwd'
                '/loadapp'
                '/qr'
            ]
                found = true if p == path || path.indexOf(p + "/") == 0
            return found

        $rootScope.getMenu = (path) ->
            return "" if path == undefined || path == ""
            menu = path.substr(1)
            f = menu.indexOf("/") 
            if f > 0
                menu = menu.substr(0, f)
            return menu
        
        $rootScope.isMissionPanel = ->
            menu = $rootScope.getMenu($rootScope.lastPath)
            return true if menu in ['missions', 'process']
            return false
        
        $rootScope.isTeamPanel = ->
            menu = $rootScope.getMenu($rootScope.lastPath)
            return true if menu in ['team']
            return false
        
        $rootScope.isCalendarPanel = ->
            menu = $rootScope.getMenu($rootScope.lastPath)
            return true if menu in ['calendar']
            return false
        
        $rootScope.isCategoryPanel = ->
            menu = $rootScope.getMenu($rootScope.lastPath)
            return true if menu in ['categories']
            return false
        
        $rootScope.isSearchPanel = ->
            menu = $rootScope.getMenu($rootScope.lastPath)
            return true if menu in ['search']
            return false

        $scope.main =
            brand: 'ハンドクラウド'

)