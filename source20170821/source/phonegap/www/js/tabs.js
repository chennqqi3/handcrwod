angular.module('app.tabs', [])

.controller('tabsCtrl', function($scope, $rootScope, $state, userStorage) {
    $rootScope.$on('$ionicView.beforeEnter', function() {
        if ($state.current.data.hideTabs) {
            $('.tabs-icon-top').addClass('tabs-hide');
        }
    });
    $rootScope.$on('$ionicView.afterEnter', function() {
        if ($state.current.data.hideTabs) {
        }
        else {
            $('.tabs-icon-top').removeClass('tabs-hide');   
        }
        userStorage.refresh_alert_label();
    });

    $rootScope.$on('reload_session', function() {
        userStorage.refresh_alert_label();
    });
});