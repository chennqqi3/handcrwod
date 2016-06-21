angular.module('app.tabs', [])

.controller('tabsCtrl', function($scope, $rootScope, $state, userStorage) {
    $rootScope.$on('$ionicView.beforeEnter', function() {
        if ($state.current.name === 'tab.tasks.edit' ||
            $state.current.name === 'tab.chatroom' ||
            $state.current.name === 'tab.chatroom.edit' ||
            $state.current.name === 'tab.chatroom.task' ||
            $state.current.name === 'tab.chatroom.task.edit' ||
            $state.current.name === 'tab.chatroom.process' ||
            $state.current.name === 'tab.chatroom.attach' ||
            $state.current.name === 'tab.chatroom.member' ||
            $state.current.name === 'tab.chatroom.member.add' ||
            $state.current.name === 'tab.chatroom.star') {
            $('.tabs-icon-top').addClass('tabs-hide');
        }
    });
    $rootScope.$on('$ionicView.afterEnter', function() {
        if ($state.current.name === 'tab.tasks.edit' ||
            $state.current.name === 'tab.chatroom' ||
            $state.current.name === 'tab.chatroom.edit' ||
            $state.current.name === 'tab.chatroom.task' ||
            $state.current.name === 'tab.chatroom.task.edit' ||
            $state.current.name === 'tab.chatroom.process' ||
            $state.current.name === 'tab.chatroom.attach' ||
            $state.current.name === 'tab.chatroom.member' ||
            $state.current.name === 'tab.chatroom.member.add' ||
            $state.current.name === 'tab.chatroom.star') {
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