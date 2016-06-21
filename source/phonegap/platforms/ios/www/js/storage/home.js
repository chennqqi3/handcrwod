angular.module('app.storage.home', [])
.factory('homeStorage', 
    function($rootScope, $api, $session, $dateutil, filterFilter, AUTH_EVENTS, $auth, $chat) {
        var accept_invite, add, bot_messages, edit, get, get_home, init, invite, open, priv, remove, remove_member, search, select;
        init = function() {
            if ($auth.isAuthenticated()) {
                return search();
            }
        };
        search = function() {
            return $api.call("home/search").then(function(res) {
                var homes;
                if (res.data.err_code === 0) {
                    $rootScope.homes = res.data.homes;
                    return $rootScope.homes;
                } else {
                    return [];
                }
            });
        };
        get_home = function(home_id) {
            var home, i, len, ref;
            ref = $rootScope.homes;
            for (i = 0, len = ref.length; i < len; i++) {
                home = ref[i];
                if (home.home_id === home_id) {
                    return home;
                }
            }
            return null;
        };
        set_home = function(home) {
            var home, i, len, ref;
            ref = $rootScope.homes;
            for (i = 0, len = ref.length; i < len; i++) {
                if (ref[i].home_id === home.home_id) {
                    ref[i] = home;
                    return;
                }
            }
        };
        add = function(home, callback) {
            return $api.call("home/add", home).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.home('add', res.data.home_id);
                    }
                }
            });
        };
        remove = function(home_id, callback) {
            var params = {
                home_id: home_id
            };
            return $api.call("home/remove", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.home('remove', home_id);
                    }
                }
            });
        };
        get = function(home_id, public_complete_flag, private_complete_flag, callback) {
            var params = {
                home_id: home_id,
                public_complete_flag: public_complete_flag,
                private_complete_flag: private_complete_flag
            };
            return $api.call("home/get", params).then(function(res) {
                var i, j, ref;
                if (res.data.err_code === 0) {
                    if (res.data.home.members.length > 0) {
                        for (i = j = 0, ref = res.data.home.members.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                            res.data.home.members[i].priv_name = $rootScope.get_priv_name(res.data.home.members[i].priv);
                        }
                    }
                }
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        edit = function(home, callback) {
            return $api.call("home/edit", home).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.home('edit', home.home_id);
                    }
                }
            });
        };
        open = function(home_id, callback) {
            var params = {
                home_id: home_id
            };
            return $api.call("home/open", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        select = function(home, callback) {
            var params = {
                home_id: home.home_id
            };
            $api.call("home/select", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
            $session.setCurHome(home);
        };
        priv = function(home_id, user_id, priv, callback) {
            var params = {
                home_id: home_id,
                user_id: user_id,
                priv: priv
            };
            return $api.call("home/priv", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        members = function(home_id, callback) {
            var params = {
                home_id: home_id
            };
            return $api.call("home/members", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        remove_member = function(home_id, user_id, callback) {
            var params = {
                home_id: home_id,
                user_id: user_id
            };
            return $api.call("home/remove_member", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.home('remove_member', home_id);
                    }
                }
            });
        };
        invite = function(req, callback) {
            return $api.call("home/invite", req).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.home('invite', req.home_id);
                    }
                }
            });
        };
        accept_invite = function(home_id, accept, callback) {
            var params = {
                home_id: home_id,
                accept: accept
            };
            return $api.call("home/accept_invite", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        bot_messages = function(home_id, self_only, callback) {
            var params;
            params = {
                home_id: home_id,
                self_only: self_only
            };
            return $api.call("home/bot_messages", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
                if (res.data.err_code === 0) {
                    return $rootScope.bot_tasks = res.data.tasks;
                }
            });
        };

        set_unreads = function(delta) {
            len = $rootScope.homes.length;
            for (i = 0; i < len; i ++) {
                home = $rootScope.homes[i];
                if (home.home_id == $rootScope.cur_home.home_id)
                    home.unreads = home.unreads + delta;
            }
            return;
        };

        upload_logo = function(home_id, file) {
            return $api.upload_file('home/upload_logo', file, {
                home_id: home_id
            });
        };

        remove_logo = function(home_id, callback) {
            var params = {
                home_id: home_id
            }

            $api.call("home/remove_logo", params)
                .then(function(res) {
                    if (callback != undefined)
                        callback(res.data);
                });
            return;
        };

        refresh_logo = function(home_id) {
            params = {
                home_id: home_id
            }

            $api.call("home/logo_url", params)
                .then(function(res) {
                    if (res.data.err_code == 0) {
                        home = get_home(home_id);
                        home.logo_url = res.data.logo_url;
                    }
                });
            return;
        };

        return {
            init: init,
            search: search,
            get_home: get_home,
            set_home: set_home,
            add: add,
            remove: remove,
            get: get,
            edit: edit,
            open: open,
            select: select,
            priv: priv,
            members: members,
            remove_member: remove_member,
            invite: invite,
            accept_invite: accept_invite,
            bot_messages: bot_messages,
            set_unreads: set_unreads,

            upload_logo: upload_logo,
            remove_logo: remove_logo,
            refresh_logo: refresh_logo
        };
    }
);