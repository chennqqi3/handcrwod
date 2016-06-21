'use strict'

angular.module('app.settings', [])

.controller('settingsCtrl', 
    ($scope, $api, $rootScope, logger, $session, $upload, CONFIG, $location, $numutil, $timeout, userStorage) ->
        $rootScope.nav_id = "settings"

        # initialize
        $scope.init = ->
            $scope.time_zones = moment.tz.names()
            $scope.alarm_times = [0..23]

            userStorage.get_profile(null, (res) ->
                if res.err_code == 0
                    $scope.user = res.user
                    $scope.user.alarm_mail_flag = $scope.user.alarm_mail_flag == 1
            )
            $api.call("google/is_connected")
                .then((res) ->
                    if res.data.err_code != 0
                        url = $location.absUrl()
                        url = url.replace(/(#[\/\w]*)/gi, '') + "#/settings"
                        $scope.google_auth_url = CONFIG.GOOGLE_CONNECT_URL + "?TOKEN=" + $session.getTOKEN() + "&redirect_url=" + encodeURIComponent(url)
                    else
                        $scope.google_auth_url = ''
                 )

        $scope.init()

        $scope.$on('reload_session', () ->
            $scope.init()
        )

        # Update profile
        $scope.changeHourlyAmount = ->
            v = $numutil.to_num($scope.user.hourly_amount)
            v = 0 if v < 0
            $timeout(->
                $scope.user.hourly_amount = v
            )

        $scope.canUpdateProfile = ->
            return $scope.form_update_profile.$valid

        $scope.updateProfile = ->
            hourly_amount = $numutil.to_num($scope.user.hourly_amount)
            $api.call("user/update_profile", 
                    user_name: $scope.user.user_name
                    email: $scope.user.email
                    skills: $scope.user.skills
                    hourly_amount: hourly_amount
                    time_zone: $scope.user.time_zone
                    alarm_mail_flag: $scope.user.alarm_mail_flag
                    alarm_time: $scope.user.alarm_time
                )
                .success((data, status, headers, config) ->
                    if data.err_code == 0
                        $session.user_name = $scope.user.user_name
                        $session.email = $scope.user.email
                        $session.time_zone = $scope.user.time_zone
                        logger.logSuccess('プロファイルが保存されました。')
                    else
                        logger.logError(data.err_msg)
                        
                    $scope.showMessage = true
                )

        # Update password
        $scope.canUpdatePassword = ->
            return $scope.form_update_password.$valid

        $scope.updatePassword = ->
             $api.call("user/update_profile", { old_password: $scope.user.old_password, new_password: $scope.user.new_password })
                .success((data, status, headers, config) ->
                    if data.err_code == 0
                        logger.logSuccess('パスワードが変更されました。')
                    else
                        logger.logError(data.err_msg)
                        
                    $scope.showMessage = true
                )

        # Upload avartar
        $scope.onUploadAvartar = (files) ->
            file = files[0]
            userStorage.upload_avartar(file).progress( (evt) ->
                #console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total))
            ).success( (data, status, headers, config) ->
                if data.err_code == 0
                    $scope.user.avartar = data.avartar
                else
                    logger.logError(data.err_msg)
            )

        # Disconnect google
        $scope.disconnectGoogle = ->
            $api.call("google/disconnect")
                .then((res) ->
                    if res.data.err_code == 0
                        url = $location.absUrl()
                        url = url.replace(/(#[\/\w]*)/gi, '') + "#/settings"
                        $scope.google_auth_url = CONFIG.GOOGLE_CONNECT_URL + "?TOKEN=" + $session.getTOKEN() + "&redirect_url=" + encodeURIComponent(url)
                 )

        return
)