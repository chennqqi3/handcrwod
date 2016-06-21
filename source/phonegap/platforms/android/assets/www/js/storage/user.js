angular.module('app.storage.user', [])

.factory('userStorage', 
    function($rootScope, $api, $session, $cordovaPush, $auth) {
        var alerts, get_profile, resend_activate_mail, signup, update_profile;
        signup = function(user, callback) {
            $api.call("user/signup", user)
                .then(function(res) {
                    if (callback !== void 0) {
                        callback(res.data);
                    }
                });
        }

        resend_activate_mail = function(user, callback) {
            $api.call("user/resend_activate_mail", user)
                .then(function(res) {
                    if (callback !== void 0) {
                        callback(res.data);
                    }
                });
        }

        activate = function(user, callback) {
            $api.call("user/activate", user)
                .then(function(res) {
                    if (callback !== void 0) {
                        callback(res.data);
                    }
                });
        }

        register_push = function(callback) {
            if ($session.user_id == undefined)
                return;

            device_type = $api.device_type();
            if (device_type == 0) {
                if (callback != undefined)
                    callback({err_code: 0});
                return;
            }

            if ($rootScope.device_token != null) {
                console.log(" user_id: " + $session.user_id + " device_type: " + device_type + " deviceToken: " + $rootScope.device_token);
                params = {
                    'user_id': $session.user_id,
                    'device_type': device_type,
                    'device_token': $rootScope.device_token
                }
                $session.statesToStorage();
                $api.call("user/set_push_token", params)
                    .then(function(res) {

                        if(res.data.err_code == 0) {
                            console.log("setting device to server success!");   
                        }
                        else
                            console.log("setting device to server fail: " + res.data.err_msg);

                        if (callback != undefined)
                            callback(res.data);
                    });
            }
            else {
                if (callback != undefined)
                    callback({err_code: 0});
            }
        }

        unregister_push = function(callback) {
            device_type = $api.device_type();
            if (device_type == 0) {
                if (callback != undefined)
                    callback({err_code: 0});
                return;
            }

            if ($rootScope.device_token != null) {
                params = {
                    'user_id': null,
                    'device_type': device_type,
                    'device_token': $rootScope.device_token
                }
                $api.call("user/set_push_token", params)
                    .then(function(res) {
                        if(res.data.err_code == 0)
                            console.log("unsetting device to server success!");
                        else
                            console.log("unsetting device to server fail: " + res.data.err_code);

                        if (callback != undefined)
                            callback(res.data);
                    });
            }
        }
        
        get_profile = function(user_id, callback) {
            var params;
            if (user_id !== null) {
                params = {
                    user_id: user_id
                };
            } else {
                params = null;
            }
            $api.call('user/get_profile', params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };

        update_profile = function(user, callback) {
            $api.call('user/update_profile', user).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        alerts = function(callback) {
            $api.call('user/alerts').then(function(res) {
                if (res.data.err_code === 0) {
                    $rootScope.alerts = res.data.alerts;

                    refresh_alert_label();
                }
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };

        upload_avartar = function(file) {         
            return $api.upload_file('user/upload_avartar', file);
        };

        refresh_alert_label = function() {
            $('.tab-nav .icon-info .sub-label').remove();

            if ($rootScope.alerts != null && $rootScope.alerts.length > 0) {
                $('.tab-nav .icon-info').append($('<span class="sub-label">' + $rootScope.alerts.length + '</span>'));
            }
        };

        return {
            signup: signup,
            resend_activate_mail: resend_activate_mail,
            activate: activate,
            register_push: register_push,
            unregister_push: unregister_push,
            get_profile: get_profile,
            update_profile: update_profile,
            alerts: alerts,
            upload_avartar: upload_avartar,
            refresh_alert_label: refresh_alert_label
        };
    }
);