'use strict'

angular.module('app.storage.home', [])

.factory('homeStorage', 
    ($rootScope, $api, $session, $dateutil, filterFilter, AUTH_EVENTS, $auth, $chat, logger) ->
        # Initialize
        init = ->
            if $auth.isAuthenticated()
                search()
        
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
                        return $rootScope.homes
                    else
                        return []
                )

        # Get home from home_id
        get_home = (home_id) ->
            for home in $rootScope.homes
                if home.home_id == home_id
                    return home
            return null

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

        select = (home, callback) ->
            params = 
                home_id: home.home_id

            $api.call("home/select", params)
                .then((res) ->
                    if res.data.err_code == 0
                        $session.setCurHome(home)            
                    else
                        logger.logError(res.data.err_msg)

                    if callback != undefined
                        callback(res.data)
                )            
            
            return

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

        set_unreads = (delta) ->
            for home in $rootScope.homes
                if home.home_id == $rootScope.cur_home.home_id
                    home.unreads = home.unreads + delta
            return

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

        return {
            init: init
            search: search
            get_home: get_home
            add: add
            remove: remove
            get: get
            edit: edit
            open: open
            select: select
            priv: priv

            remove_member: remove_member
            
            invite: invite
            accept_invite: accept_invite

            set_unreads: set_unreads

            upload_logo: upload_logo
            remove_logo: remove_logo
            refresh_logo: refresh_logo
        }
)
