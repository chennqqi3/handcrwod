'use strict'

angular.module('app.signout', [])

# for toggle task edit panel
.controller('signoutCtrl', 
    ($scope, $rootScope, $auth, $location, AUTH_EVENTS) ->
    	$rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
    	$auth.logout()
    	$location.path('/signin')
)