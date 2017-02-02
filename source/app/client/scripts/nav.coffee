'use strict';

angular.module('app.controller.nav', [])

.controller('NavCtrl', 
    ($scope, $rootScope, $session, AUTH_EVENTS, $timeout, $location, $dialogs, $route, missionStorage, logger, $api, $window) ->
        app = $('#app')

        $scope.init = ()->
            $scope.session = $session
            $scope.loaded = false

            $api.hide_tutorial()

            $timeout(() ->
                $scope.loaded = true

                $timeout(->
                    # チュートリアル
                    if $session.tutorial
                        mem_count = 0
                        for mission in $rootScope.missions
                            if mission.private_flag == 2 && mission.user_id != $session.user_id
                                mem_count++

                        if mem_count == 0
                            $('#btn_nav_invite_member').tutpop(
                                content: 'グループにメンバーがいません。こちらから他人をグループに招待してください。'
                            ).tutpop('show').on('close.tutpop', $api.close_tutorial)

                        else
                            ms_count = 0
                            for mission in $rootScope.missions
                                if mission.private_flag == 0 || mission.private_flag == 1
                                    ms_count++                    

                            if ms_count < 2
                                $('#btn_add_mission').tutpop(
                                    content: '新しいルームを作成するには、こちらをクリックしてください。'
                                ).tutpop('show').on('close.tutpop', $api.close_tutorial)
                , 1000)
            )
           
        $scope.init()

        $scope.$on(AUTH_EVENTS.loginSuccess, (event, count) ->
            $scope.init()
        )

        $scope.$on('reload_session', (event, count) ->
            $scope.init()
        )

        $scope.$on('synced-server', (event, count) ->
            $scope.init()
        )

        $scope.$on('select-home', (event) ->
            $scope.init()
        )

        $scope.$on('mission-search-post', (event) ->
            $scope.init()
        )

        $scope.$on('refresh-nav', (event) ->
            $scope.init()
        )

        $scope.showAlerts = ->
            $dialogs.showAlerts()

        # Mission related
        $scope.addMission = () ->
            $dialogs.addMission()
            $api.hide_tutorial()
            return

        $scope.openMission = (private_flag) ->
            $dialogs.openMission(private_flag)
            return

        $scope.pinMission = (mission_id) ->
            if mission_id == null
                return

            mission = missionStorage.get_mission(mission_id)
            if mission == null
                return

            if mission.pinned == 1
                pinned = 0
            else
                pinned = 1
            missionStorage.pin(mission_id, pinned, (res) ->
                if res.err_code == 0
                    mission.pinned = pinned
                    id = "#" + missionStorage.mission_html_id(mission)
                    if mission.pinned
                        $(id + ' .pin').removeClass('btn-pin')
                    else
                        $(id + ' .pin').addClass('btn-pin')
                else
                    logger.logError(res.err_msg)
            )
            return

        # グループへの招待
        $scope.inviteMember = ->
            $dialogs.inviteHome($rootScope.cur_home)
            $api.hide_tutorial()

        # toggle group
        $scope.groups = [true, true, true]
        $scope.toggleGroup = (index) ->
            $scope.groups[index] = !$scope.groups[index]
            $scope.refreshGroup(index)

        $scope.refreshGroup = (index) ->
            show = $scope.groups[index]
            if $rootScope.missions
                for mission in $rootScope.missions
                    id = missionStorage.mission_html_id(mission)
                    set = false
                    if index == 0
                        if mission.private_flag == 0 || mission.private_flag == 1
                            set = true
                    else if index == 2
                        if mission.private_flag == 2 && mission.user_id != $session.user_id
                            set = true

                    if set
                        if show
                            $('#' + id).removeClass('hide')
                        else
                            if !mission.visible
                                $('#' + id).addClass('hide')

        $scope.selectLogout = ->
            message = "ログアウトします。よろしいでしょうか？"
            $dialogs.confirm('ログアウト', message, 'ログアウト', "logout")
            return

        $scope.$on('logout', () ->
            app.removeClass('expanded')
            $location.path("/signout")
        )

        # search
        $scope.search_string = ''
        $scope.onSelectSearchMessage = (message) ->
            if !$api.is_empty($scope.cur_mission) && message.mission_id == $scope.cur_mission.mission_id
                $rootScope.$broadcast('scroll-to-message', message.cmsg_id)
            else
                $location.path("/chats/" + message.mission_id + "/" + message.cmsg_id)
            return

        $scope.search = () ->
            if !$api.is_empty($scope.search_string)
                $dialogs.chatSearch(false, $scope.search_string, $scope.onSelectSearchMessage)
                $scope.search_string = ''
                $scope.changeSearch()
            return

        $scope.exitSearch = () ->
            $scope.search_string = ''
            $scope.changeSearch()

        $scope.changeSearch = () ->
            if $api.is_empty($scope.search_string)
                $scope.refreshGroup(0)
                $scope.refreshGroup(2)
            else
                if $rootScope.missions
                    for mission in $rootScope.missions
                        id = missionStorage.mission_html_id(mission)
                        if mission.mission_name.indexOf($scope.search_string) > -1 || mission.login_id && mission.login_id.indexOf($scope.search_string) > -1
                            $('#' + id).removeClass('hide')
                        else
                            $('#' + id).addClass('hide')

        $scope.$on('unread-message', (event, mission) ->
            id = "#" + missionStorage.mission_html_id(mission)
            $(id + ' .unreads').html(missionStorage.mission_unreads_to_html(mission))

            if (mission.private_flag == 0 || mission.private_flag == 1)
                $(".list-mission").prepend($(id))
            else
                $(".list-team-member").prepend($(id))

            missionStorage.check_hidden_unreads()
        )

        angular.element($window).bind('resize', ->
            missionStorage.check_hidden_unreads()
        )

        $scope.onScroll = ->
            missionStorage.check_hidden_unreads()

        $scope.open_member = (user_id) ->
            if $rootScope.missions
                for mission in $rootScope.missions
                    if mission.private_flag == 2 && mission.user_id == user_id
                        if mission.mission_id != null
                            $location.path("/chats/" + mission.mission_id)
                        else
                            missionStorage.open_member($rootScope.cur_home.home_id, user_id, (res) ->
                                if res.err_code == 0
                                    new_mission_id = res.mission_id
                                    missionStorage.search($rootScope.cur_home.home_id, $rootScope.cur_home.include_completed).then(()->
                                        $location.path("/chats/" + new_mission_id)
                                    )
                                else
                                    logger.logError(res.err_msg)
                            )  

        $scope.scroll_unread = () -> 
            top = missionStorage.get_top_of_hidden_unreads()
            h = $('#nav').height()
            h_avartar = $('.nav-bar .my-avartar').height()
            h_logout = $('.nav-bar .logout').height()
            h_unread = $('.nav-bar .unread_hint').height()
            h = h - h_avartar - h_logout - h_unread
            top = if top > h then (top - h ) else h
            $('#nav').animate({scrollTop: top}, 100);
            return
)


.controller('NavHomeCtrl', 
    ($scope, $rootScope, $session, AUTH_EVENTS, $route, homeStorage, logger, $dialogs, $location, $timeout, $api) ->
        app = $('#app')

        $scope.init = ()->
            $scope.session = $session
            $scope.loaded = false
            $timeout(() ->
                $scope.loaded = true
            )

        $scope.open = (home_id) ->
            $location.path('/home/' + home_id)
            return
           
        $scope.init()

        $scope.$on(AUTH_EVENTS.loginSuccess, (event, count) ->
            $scope.init()
        )

        $scope.$on('reload_session', (event, count) ->
            $scope.init()
        )

        $scope.$on('synced-server', (event, count) ->
            $scope.init()
        )

        $scope.$on('refresh-home', (event, home) ->
            id = "#" + homeStorage.home_html_id(home)
            $(id).html(homeStorage.home_to_html(home, false))
            $(".home-list").prepend($(id))
        )

        $scope.$on('home-search-post', (event) ->
            $scope.init()
        )

        # グループ追加  
        $scope.addHome = ->
            $dialogs.addHome()
            $api.hide_tutorial()
            return

        $scope.$on('added_home', (event, home) ->
            $rootScope.$broadcast('refresh-homes')
            if $rootScope.cur_home == null
                $location.path('/home/' + home.home_id)
            return
        )
)

.directive('chatRooms', ($rootScope, $compile, $filter, $session, $timeout, missionStorage, HPRIV) ->
    ###
    <li class="hc-bot" ng-class="{'active': nav_id=='chatroom_' + bot_mission.mission_id }">
        <a href="#/chats/{{bot_mission.mission_id}}">
            <div class="info">
                <span class="badge badge-danger" ng-show="bot_mission.unreads > 0">{{bot_mission.unreads}}</span>
            </div>
            <i class="icon-emotsmile"></i><span>アシスタント</span>
        </a>
    </li>
    <li ng-if="cur_home != null">
        <a href="javascript:;" title="チャットルーム新規作成" ng-click="addMission()" class="right-button" ng-show="cur_home.priv == HPRIV.HMANAGER"><i class="icon-plus"></i></a>
        <a href="javascript:;" ng-click="toggleGroup(0)"><i class="icon-bubbles"></i><span>ルーム</span></a>
        <ul class="list-mission">
            <li data-ng-repeat="mission in missions0 = (missions | filter:roomFilter | filter:{mission_name:search_string} | orderBy:'order') track by mission.mission_id" class="list-item" ng-class="{'active': nav_id=='chatroom_' + mission.mission_id }" data-mission-id="{{mission.mission_id}}" ng-show="search_string !='' && search_string != null || groups[0] || mission.visible">
                <div class="info">
                    <i class="badge badge-danger" ng-show="mission.unreads > 0">{{mission.unreads}}</i>
                    <a href="javascript:;" ng-class="{'btn-pin': mission.pinned!=1}" ng-click="pinMission(mission)"><i class="icon-pin"></i></a>
                </div>
                <a ng-href="#/chats/{{mission.mission_id}}" title="{{::mission.mission_name}}"><i class="fa fa-lock" ng-if="mission.private_flag==1"></i> {{mission.mission_name}}</a>
            </li>
            <li ng-show="missions1.length > (missions1 | filter:visibleFilter).length || missions0.length > (missions0 | filter:visibleFilter).length">
                <a href="javascript:;" ng-click="toggleGroup(0)" class="more-link"><span ng-show="!groups[0]"><i class="fa fa-chevron-down"></i> すべて開く</span><span ng-show="groups[0]"><i class="fa fa-chevron-up"></i> 最新だけを表示...</span></a>
            </li>
        </ul>
    </li>
    <li ng-if="cur_home != null">
        <a href="javascript:;" title="メンバーを招待" ng-click="inviteMember()" class="right-button" ng-if="cur_home.priv == HPRIV.HMANAGER"><i class="icon-plus"></i> </a>
        <a href="javascript:;" ng-click="toggleGroup(2)"><i class="icon-people"></i><span>メンバー</span></a>
        <ul class="list-team-member">
            <li data-ng-repeat="mission in missions2 = (missions | filter:memberFilter | filter:{mission_name:search_string} | orderBy:'order') track by mission.user_id" class="list-item" ng-class="{'active': nav_id=='chatroom_' + mission.mission_id }" data-mission-id="{{mission.mission_id}}" ng-show="search_string !='' && search_string != null || groups[2] || mission.visible">
                <img alt="" ng-src="{{mission.avartar}}" class="avartar">
                <div class="info">
                    <i class="badge badge-danger" ng-show="mission.unreads > 0">{{mission.unreads}}</i>
                    <a href="javascript:;" ng-class="{'btn-pin': mission.pinned!=1}" ng-click="pinMission(mission)"><i class="icon-pin"></i></a>
                </div>
                <a href="javascript:;" ng-click="open_member(mission)" title="{{::mission.mission_name}}">{{mission.mission_name}}</a>
            </li>
            <li ng-show="missions2.length > (missions2 | filter:visibleFilter).length">
                <a href="javascript:;" ng-click="toggleGroup(2)" class="more-link"><span ng-show="!groups[2]"><i class="fa fa-chevron-down"></i> すべて開く</span><span ng-show="groups[2]"><i class="fa fa-chevron-up"></i> 最新だけを表示...</span></a>
            </li>
        </ul>
    </li>
    ###
    getTemplate = (scope) ->
        n = 0
        template = ''

        if $rootScope.cur_home != null
            template += '<li>'
            if ($rootScope.cur_home.priv == HPRIV.HMANAGER) 
                template += '<a id="btn_add_mission" href="javascript:;" title="チャットルーム新規作成" ng-click="addMission()" class="right-button"><i class="icon-plus"></i></a>'
            template += '    <a href="javascript:;" ng-click="toggleGroup(0)"><i class="icon-bubbles"></i><span>ルーム</span></a>'
            template += '    <ul class="list-mission">'

            if $rootScope.missions
                t = 0
                v = 0
                for mission in $rootScope.missions
                    if !(mission.private_flag == 0 || mission.private_flag == 1)
                        continue
                    t += 1
                    if mission.visible 
                        v +=1
                    template += missionStorage.mission_to_html(mission, scope.groups)

            if t > v        
                template += '    <li>'
                template += '        <a href="javascript:;" ng-click="toggleGroup(0)" class="more-link"><span ng-show="!groups[0]"><i class="fa fa-chevron-down"></i> すべて開く</span><span ng-show="groups[0]"><i class="fa fa-chevron-up"></i> 最新だけを表示...</span></a>'
                template += '    </li>'
            template += '    </ul>'
            template += '</li>'       

            template += '<li>'
            if ($rootScope.cur_home.priv == HPRIV.HMANAGER) 
                template += '<a id="btn_nav_invite_member" href="javascript:;" title="メンバーを招待" ng-click="inviteMember()" class="right-button"><i class="icon-plus"></i></a>'
            template += '    <a href="javascript:;" ng-click="toggleGroup(2)"><i class="icon-people"></i><span>メンバー</span></a>'
            template += '    <ul class="list-team-member">'

            if $rootScope.missions
                t = 0
                v = 0
                for mission in $rootScope.missions
                    if (!(mission.private_flag == 2 && mission.user_id != $session.user_id && mission.accepted == 1))
                        continue

                    t += 1
                    if mission.visible 
                        v +=1
                    template += missionStorage.mission_to_html(mission, scope.groups)

            if t > v 
                template += '    <li>'
                template += '        <a href="javascript:;" ng-click="toggleGroup(2)" class="more-link"><span ng-show="!groups[2]"><i class="fa fa-chevron-down"></i> すべて開く</span><span ng-show="groups[2]"><i class="fa fa-chevron-up"></i> 最新だけを表示...</span></a>'
                template += '    </li>'
            template += '    </ul>'
            template += '</li>'

        return template

    linker = (scope, element, attrs) ->
        element.html(getTemplate(scope))
        $compile(element.contents())(scope)

        $timeout(->
            missionStorage.check_hidden_unreads()
        )

    return {
        restrict: "E"
        replace: true
        link: linker
    }
)

.directive('homeList', ($rootScope, $compile, $filter, $session, homeStorage) ->
    ###
    <li ng-repeat="home in homes | orderBy:'order'" ng-class="{'active': cur_home.home_id==home.home_id }" title="{{home.home_name}}" ng-click="open(home)">
        <span ng-if="home.logo_url == null">{{home.home_name | abbr}}</span>
        <img ng-src="{{home.logo_url}}" class="img30_30 logo" ng-if="home.logo_url != null">
        <i class="badge badge-danger" ng-show="home.unreads > 0">{{home.unreads}}</i>
    </li>
    ###
    getTemplate = (scope) ->
        template = ''

        if $rootScope.homes != null
            for home in $rootScope.homes
                template += homeStorage.home_to_html(home)

        template += '<li id="add_home" title="グループ新規作成" ng-click="addHome()">'
        template += '    <span><i class="icon-plus"></i></span>'
        template += '</li>'

        return template

    linker = (scope, element, attrs) ->
        element.html(getTemplate(scope))
        $compile(element.contents())(scope)

    return {
        restrict: "E"
        replace: true
        link: linker
    }
)