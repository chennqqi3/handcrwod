angular.module('app.storage.home', [])
.factory('homeStorage', 
    function($rootScope, $api, $session, $chat, $ionicPopup) {
        var accept_invite, add, bot_messages, edit, get, get_home, invite, open, priv, remove, remove_member, search, select, invite_from_qr;
        search = function() {
            return $api.call("home/search").then(function(res) {
                var homes;
                if (res.data.err_code === 0) {
                    $rootScope.homes = res.data.homes;
                    $rootScope.homes.sort(function(a, b) { return a.order - b.order;});
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
                if (home.home_id == home_id) {
                    return home;
                }
            }
            return null;
        };
        set_home = function(home) {
            var home, i, len, ref;
            ref = $rootScope.homes;
            for (i = 0, len = ref.length; i < len; i++) {
                if (ref[i].home_id == home.home_id) {
                    if (ref[i] != home) {
                        home.order = ref[i].order;
                        ref[i] = home;
                    }
                    return;
                }
            }
            $rootScope.homes.push(home);
        };
        set_cur_home = function(home, toStorage) {
            var new_home_id, old_home_id;
            if (toStorage == undefined) {
                toStorage = true;
            }
            old_home_id = null;
            new_home_id = null;
            if ($rootScope.cur_home == undefined) {
                $rootScope.cur_home = null;
            }
            if ($rootScope.cur_home != null) {
                old_home_id = $rootScope.cur_home.home_id;
            }
            if (home != null && home.home_id != null) {
                new_home_id = home.home_id;
            }
            $rootScope.cur_home = home;
            if ($rootScope.cur_home)
                set_home($rootScope.cur_home);

            if (toStorage) {
                $session.statesToStorage();
            }
            if (old_home_id != new_home_id) {
                $rootScope.$broadcast('select-home');
            }
        };
        add = function(home, callback) {
            return $api.call("home/add", home).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.home('add', res.data.home.home_id);
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

        break_home = function(home_id, callback) {
            params = {
                home_id: home_id
            }

            $api.call("home/break_home", params)
                .then(function(res) {
                    if (callback != undefined)
                        callback(res.data);
                });
        };

        break_handcrowd = function(callback) {
            $api.call("home/break_handcrowd")
                .then(function(res) {
                    if (callback != undefined)
                        callback(res.data);
                });
        };

        get_name = function(home_id, callback) {
            var params = {
                home_id: home_id
            };
            return $api.call("home/get_name", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
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
            set_cur_home(home);
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
        self_invite = function(home_id, invite_key, callback) {
            var req = {
                home_id: home_id,
                invite_key: invite_key
            }
            return $api.call("home/self_invite", req).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
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

        emoticons = function(home_id) {
            params = {
                home_id: home_id
            }

            $api.call("home/emoticons", params)
                .then(function(res) {
                    if (res.data.err_code == 0) {
                        for (i = 0; i<res.data.emoticons.length; i ++) {
                            icon = res.data.emoticons[i];
                            $api.init_emoticon(icon);
                        }

                        $rootScope.emoticons = res.data.emoticons;
                    }
                });
            return;
        };

        invite_from_qr = function(home_id, invite_key, callback) {
            home = get_home(home_id);
            if (home != null)
                select(home);
            else {
                get_name(home_id, function(res) {
                    if (res.err_code == 0 && !$api.is_empty(res.home_name)) {
                        $ionicPopup.confirm({
                            title: 'グループ招待',
                            template: 'グループ「' + res.home_name + '」に参加します。よろしいでしょうか？',
                            buttons: [
                                { text: 'キャンセル' },
                                {
                                    text: '<b>OK</b>',
                                    type: 'button-positive',
                                    onTap: function(e) {
                                        self_invite(home_id, invite_key, function(res) {
                                            if (res.err_code == 0) {
                                                search().then(function() {
                                                    home = get_home(home_id);
                                                    if (home != null)
                                                        select(home);
                                                });
                                            }
                                            else
                                                logger.logError(res.err_msg);
                                        });
                                    }
                                }
                            ]
                        });
                    }
                });
            }
        };

        return {
            search: search,
            get_home: get_home,
            set_home: set_home,
            set_cur_home: set_cur_home,
            add: add,
            remove: remove,
            get_name: get_name,
            get: get,
            edit: edit,
            open: open,
            select: select,
            priv: priv,
            members: members,
            remove_member: remove_member,
            invite: invite,
            self_invite: self_invite,
            
            accept_invite: accept_invite,
            bot_messages: bot_messages,

            break_home: break_home,
            break_handcrowd: break_handcrowd,

            upload_logo: upload_logo,
            remove_logo: remove_logo,
            refresh_logo: refresh_logo,

            emoticons: emoticons,

            invite_from_qr: invite_from_qr
        };
    }
);