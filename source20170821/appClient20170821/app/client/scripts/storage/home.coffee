'use strict'

angular.module('app.storage.home', [])

.factory('homeStorage', 
    ($rootScope, $api, $session, $dateutil, $filter, $chat, logger) ->
        # Search homes
        search = () ->
            $api.call("home/search")
                .then((res) ->
                    if res.data.err_code == 0
                        homes = res.data.homes
                        # reorder
                        order = 0
                        for home in homes
                            home.order = order
                            order += 1

                        $rootScope.homes = homes

                        $rootScope.$broadcast('home-search-post')

                        return $rootScope.homes
                    else
                        return []
                )

        add = (home, callback) ->
            $api.call("home/add", home)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.home('add', res.data.home.home_id)
                )

        remove = (home_id, callback) ->
            params =
                home_id: home_id

            $api.call("home/remove", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.home('remove', home_id)
                )

        break_home = (home_id, callback) ->
            params =
                home_id: home_id

            $api.call("home/break_home", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        break_handcrowd = (callback) ->
            $api.call("home/break_handcrowd")
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        # Get home from home_id
        get_home = (home_id) ->
            if $rootScope.homes
                for home in $rootScope.homes
                    if home.home_id == home_id
                        return home
            return null

        set_home = (home) ->
            if $rootScope.homes != null && $rootScope.homes.length > 0
                for i in [0..$rootScope.homes.length - 1]
                    if $rootScope.homes[i].home_id == home.home_id
                        if $rootScope.homes[i] != home
                            home.order = $rootScope.homes[i].order
                            $rootScope.homes[i] = home
                        return
            $rootScope.homes.push(home)
            return

        set_cur_home = (home, toStorage) ->
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
            select_home_in_nav()
            if $rootScope.cur_home
                set_home($rootScope.cur_home)

            if toStorage
                $session.statesToStorage()
            
            if old_home_id != new_home_id
                $rootScope.$broadcast('select-home')
            return

        get_name = (home_id, callback) ->
            params =
                home_id: home_id

            $api.call("home/get_name", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        get = (home_id, public_complete_flag, private_complete_flag, callback) ->
            params = 
                home_id: home_id
                public_complete_flag: public_complete_flag
                private_complete_flag: private_complete_flag

            $api.call("home/get", params)
                .then((res) ->
                    if res.data.err_code == 0
                        if res.data.home.members.length > 0
                            for i in [0 .. res.data.home.members.length - 1]
                                res.data.home.members[i].priv_name = $rootScope.get_priv_name(res.data.home.members[i].priv)

                    if callback != undefined
                        callback(res.data)
                )

        edit = (home, callback) ->
            $api.call("home/edit", home)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.home('edit', home.home_id)
                )

        open = (home_id, callback) ->
            params = 
                home_id: home_id

            $api.call("home/open", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        priv = (home_id, user_id, priv, callback) ->
            params = 
                home_id: home_id
                user_id: user_id
                priv: priv

            $api.call("home/priv", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        remove_member = (home_id, user_id, callback) ->
            params = 
                home_id: home_id
                user_id: user_id

            $api.call("home/remove_member", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.home('remove_member', home_id, user_id)
                )

        invite = (req, callback) ->
            $api.call("home/invite", req)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.home('invite', req.home_id)
                )

        self_invite = (home_id, invite_key, callback) ->
            req = 
                home_id: home_id
                invite_key: invite_key
            $api.call("home/self_invite", req)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

        accept_invite = (home_id, accept, callback) ->
            params = 
                home_id: home_id
                accept: accept

            $api.call("home/accept_invite", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.home('accept_invite', home_id)
                )

        upload_logo = (home_id, file) ->
            $api.upload_file('home/upload_logo', file, {
                home_id: home_id
            })

        remove_logo = (home_id, callback) ->
            params =
                home_id: home_id

            $api.call("home/remove_logo", params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        refresh_logo = (home_id) ->
            params =
                home_id: home_id

            $api.call("home/logo_url", params)
                .then((res) ->
                    if res.data.err_code == 0
                        home = get_home(home_id)
                        home.logo_url = res.data.logo_url
                )
            return

        home_html_id = (home) ->
            return 'home_' + home.home_id

        select_home_in_nav = () ->
            $('#navHome li').removeClass('active')
            if $rootScope.cur_home
                id = home_html_id($rootScope.cur_home)
                $('#' + id).addClass('active')
            return

        home_unreads_to_html = (home) ->
            txt = ''
            if (home.unreads > 0)
                txt += '<i class="badge badge-danger">' + home.unreads + '</i> '

            if (home.to_unreads > 0)
                txt += '<br/><i class="badge badge-success to"><small>TO</small>' + home.to_unreads + '</i> '

            return txt

        home_to_html = (home, include_self) ->
            html = ""
            if (include_self == undefined)
                include_self = true

            home_id = home.home_id
            ###
            <li ng-repeat="home in homes | orderBy:'order'" ng-class="{'active': cur_home.home_id==home.home_id }" title="{{home.home_name}}" ng-click="open(home)">
                <span ng-if="home.logo_url == null">{{home.home_name | abbr}}</span>
                <img ng-src="{{home.logo_url}}" class="img30_30 logo" ng-if="home.logo_url != null">
                <i class="badge badge-danger" ng-show="home.unreads > 0">{{home.unreads}}</i>
            </li>
            ###
            item_class = ' '
            if ($rootScope.cur_home && $rootScope.cur_home.home_id == home.home_id)
                item_class += 'active ';

            if (include_self)
                html += '<li id="' + home_html_id(home) + '" class="' + item_class + '" title="' + home.home_name + '" ng-click="open(' + home.home_id + ')">';

            if ($api.is_empty(home.logo_url))
                html += '    <span>' + $filter('abbr')(home.home_name) + '</span>'
            else
                html += '    <img src="' + home.logo_url + '" class="img30_30 logo">'
            html += '        <span class="unreads">' + home_unreads_to_html(home) + '</span>'
            if (include_self)
                html += '</li>'

            return html

        return {
            search: search
            add: add
            remove: remove
            get_home: get_home
            set_home: set_home
            set_cur_home: set_cur_home
            get_name: get_name
            get: get
            edit: edit
            open: open
            priv: priv

            remove_member: remove_member

            invite: invite
            accept_invite: accept_invite
            self_invite: self_invite

            upload_logo: upload_logo
            remove_logo: remove_logo
            refresh_logo: refresh_logo

            break_home: break_home
            break_handcrowd: break_handcrowd

            home_html_id: home_html_id
            select_home_in_nav: select_home_in_nav
            home_unreads_to_html: home_unreads_to_html
            home_to_html: home_to_html
        }
)
