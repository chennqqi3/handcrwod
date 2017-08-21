angular.module('app.storage.user', [])

.factory('userStorage',
    ($rootScope, $api, $session) ->
        resend_activate_mail = (user, callback) ->
            $api.call('user/resend_activate_mail', user)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        get_profile = (user_id, callback) ->
            if user_id != null
                params =
                    user_id: user_id
            else
                params = null

            $api.call('user/get_profile', params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        update_profile = (user, callback) ->
            $api.call('user/update_profile', user)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        alerts = (callback) ->
            $api.call('user/alerts')
                .then((res) ->
                    if res.data.err_code == 0
                        $rootScope.alerts = res.data.alerts
                    if callback != undefined
                        callback(res.data)
                )
            return

        unreads = (callback) ->
            $api.call('user/unreads')
                .then((res) ->
                    if res.data.err_code == 0
                        $rootScope.unreads = res.data.unreads
                    if callback != undefined
                        callback(res.data)
                )
            return

        upload_avartar = (file) ->
            $api.upload_file('user/upload_avartar', file)

        return {
            resend_activate_mail: resend_activate_mail
            get_profile: get_profile
            update_profile: update_profile
            alerts: alerts
            unreads: unreads
            upload_avartar: upload_avartar
        }
)