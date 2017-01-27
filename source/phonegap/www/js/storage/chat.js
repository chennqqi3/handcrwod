var g_messages = null;

angular.module('app.storage.chat', [])

.factory('chatStorage', 
    function($api, $session, $dateutil, $rootScope, CONFIG, $filter, chatizeService) {
        var cancel_upload_file, messages, read_messages, refresh_unreads_title, remove_message, search_messages, search_read, set_message, sound_alert, star_message, upload_file, is_to_mine;
        save_cache_messages_to_storage = function () {
            if ($session.user_id) {
                try {
                    if (g_messages != null) {
                        key = 'messages_' + $session.user_id + "_";
                        for (sub_key in g_messages) {
                            encoded = JSON.stringify(g_messages[sub_key]);
                            localStorage.setItem(key + sub_key, encoded);
                            console.log("save cache messages " + key + sub_key + encoded);
                        }
                    }
                }
                catch (err) {
                }
            }
        }

        read_cache_messages_from_storage = function(mission_id) {
            messages = null;
            if ($session.user_id) {
                key = 'messages_' + $session.user_id + "_m_" + mission_id;
                try {
                    messages = JSON.parse(localStorage.getItem(key) || []);
                    console.log("load messages " + messages.length + " " + key)
                }
                catch (err) {
                }
            }

            if (messages == null)
                return [];

            return messages;
        }

        cache_messages = function(mission_id, messages) {
            if (g_messages == null) {
                g_messages = [];
            }

            if (messages === undefined) {
                // read
                messages = g_messages["m_" + mission_id];
                if (messages == undefined) {
                    messages = read_cache_messages_from_storage(mission_id);
                    g_messages["m_" + mission_id] = messages;
                }
                console.log("message length " + messages.length + " read m_" + mission_id)
            }
            else {
                if (messages == null)
                    messages = [];

                if (messages.length > 200)
                    tmp = messages.slice(messages.length - 200);
                else
                    tmp = messages.slice();
                // write
                g_messages["m_" + mission_id] = tmp;
                console.log("message length " + g_messages["m_" + mission_id].length + " write m_" + mission_id);
            }

            return messages;
        }

        is_to_mine = function(message) {
            if ($api.is_empty(message))
                return false;

            message.content += "";
            if ($api.is_empty(message.content))
                return false;

            mine = false;
            message.content.replace(/\[to:([^\]]*)\]/g, function(item, user_id) {
                if ($session.user_id + '' == user_id || 'all' == user_id)
                    mine = true;
            });

            return mine;
        } 

        /*
            <div ng-repeat="message in messages" class="message-wrapper" ng-class="{'to-mine': isToMine(message), 'linked': message.cmsg_id==chat_id}" on-hold="onMessageHold($event, $index, message)">

                <div id="chat_{{message.cmsg_id}}">
                    <img class="profile-pic left" ng-src="{{::message.avartar}}" onerror="onProfilePicError(this)" ng-if="message.show_avartar" ng-click="showUserProfile(message.avartar)"/>

                    <div class="chat-message left" ng-class="{'me': session.user_id == message.user_id, 'border-top': message.show_avartar, 'editing': message.editing}">
                        <div class="sending" ng-if="message.cmsg_id < 0"><span>メッセージ送信中 <i class="fa fa-spinner fa-spin"></i></span></div>
                        <div class="message-detail">
                            <span class="bold" ng-if="message.show_avartar" ng-click="showUserProfile(message.avartar)">{{::message.user_name}}</span> 
                            <span class="misc"><i class="fa fa-circle text-danger {{message.read_class}}"></i><a href="javascript:;" class="star" ng-click="star(message)"><i class="fa fa-star text-warning" ng-show="message.star"></i></a> {{message.date_label}}</span>
                        </div>

                        <div class="message" ng-bind-html="message.content | chatize">
                        </div>
                    </div>
                </div>

                <div class="cf"></div>
            </div>
        */
        message_to_html = function(message, chat_id, uninclude_self) {
            html = "";

            cls = "";
            if (is_to_mine(message))
                cls += ' to-mine';
            if (message.cmsg_id == chat_id)
                cls += ' linked';

            avartar_cls = "";
            if (!message.show_avartar)
                avartar_cls += " hide";

            message_cls = "";
            if ($session.user_id == message.user_id)
                message_cls += ' me';
            if (message.show_avartar)
                message_cls += ' border-top';
            if (message.editing)
                message_cls += ' editing';

            sending_cls = "";
            if (message.cmsg_id >= 0)
                sending_cls += " hide";

            username_cls = "";
            if (!message.show_avartar)
                username_cls += " hide";

            star_cls = "";
            if (!message.star)
                star_cls += " hide";

            date_label = message.date_label;
            if (date_label == undefined)
                date_label = '';

            if (uninclude_self != true)
                html += '<div id="chat_' + message.cmsg_id + '" >';
            html += '<div class="message-wrapper' + cls + '" data-cmsg_id="' + message.cmsg_id + '">';
            html += '   <img class="profile-pic left' + avartar_cls + '" src="' + message.avartar + '" onerror="onProfilePicError(this)" onclick="onClickAvartar(\'' + message.avartar + '\')"/>';
            html += '   <div class="chat-message left' + message_cls + '">';
            html += '       <div class="sending' + sending_cls + '"><span>メッセージ送信中 <i class="fa fa-spinner fa-spin"></i></span></div>';
            html += '       <div class="message-detail">';
            html += '           <span class="bold' + username_cls + '" onclick="onClickAvartar(\'' + message.avartar + '\')">' + message.user_name + '</span>';
            html += '           <span class="misc"><i class="unread-mark fa fa-circle text-danger ' + message.read_class + '"></i><a href="javascript:;" class="star" ng-click="star(message)"><i class="fa fa-star text-warning' + star_cls + '"></i></a> ' + date_label + '</span>';
            html += '       </div>';
            html += '       <div class="message">' + $filter('chatize')(message.content);
            if (message.reacts && message.reacts.length > 0) {
                html += '       <ul class="reacts">';
                for (var i = 0; i < message.reacts.length; i ++) {
                    react = message.reacts[i];
                    rtitle = "";
                    if (react[2]) {
                        for(var j=0; j<react[2].length; j++) {
                            u = react[2][j];
                            if (rtitle != "")
                                rtitle += ",";
                            rtitle += u[1];
                        }
                        rtitle = ' title="' + rtitle + '"';
                    }
                    html += '       <li ng-click="react(' + message.cmsg_id + ', ' + react[0] + ')" ' + rtitle + '>' + chatizeService.emoticon(react[0]) + react[1] + '</li>';
                }
                html += '       </ul>';
            }
            html += '       </div>';
            html += '   </div>';
            html += '   <div class="cf"></div>';
            html += '</div>';
            if (uninclude_self != true)
                html += '</div>';

            return html;
        }

        messages_to_html = function(messages, chat_id) {
            html = "";
            messages.forEach(function(message) {
                html += message_to_html(message, chat_id);
            });

            return html;
        }

        messages = function(mission_id, prev_id, next_id, star) {
            var params;
            params = {
                home_id: $rootScope.cur_home.home_id,
                mission_id: mission_id,
                prev_id: prev_id,
                next_id: next_id,
                star: star,
                limit: 60
            };
            return $api.call("chat/messages", params).then(function(res) {
                var prev_date, user_id;
                if (res.data.err_code === 0) {
                    messages = res.data.messages;
                    user_id = null;
                    prev_date = null;
                    messages.forEach(function(cmsg) {
                        var date_label;
                        cmsg.content = cmsg.content + '';
                        cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg";
                        cmsg.read_class = (cmsg.unread ? "unread" : "read");
                        date_label = $dateutil.ellipsis_time_str(cmsg.date, prev_date);
                        prev_date = cmsg.date;
                        cmsg.date_label = date_label;
                        if (cmsg.user_id !== user_id) {
                            cmsg.show_avartar = true;
                            user_id = cmsg.user_id;
                        } else {
                            cmsg.show_avartar = false;
                        }
                    });
                    $rootScope.read_message_offset = 0;
                    refresh_unreads_title();
                    return messages;
                } else {
                    return [];
                }
            });
        };
        search_messages = function(home_id, mission_id, search_string, prev_id, next_id) {
            var params;
            params = {
                home_id: home_id,
                mission_id: mission_id,
                search_string: search_string,
                prev_id: prev_id,
                next_id: next_id
            };
            return $api.call("chat/search_messages", params).then(function(res) {
                var prev_date, user_id;
                if (res.data.err_code === 0) {
                    messages = res.data.messages;
                    messages.forEach(function(cmsg) {
                        cmsg.content = cmsg.content + '';
                        cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg";
                        cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null);
                        if ($rootScope.cur_home.home_id !== cmsg.home_id) {
                            cmsg.mission_name = cmsg.mission_name + "(" + cmsg.home_name + ")";
                        }
                    });
                    return messages;
                } else {
                    return [];
                }
            });
        };
        star_messages = function(prev_id, next_id) {
            var params;
            params = {
                prev_id: prev_id,
                next_id: next_id,
                limit: 30
            };
            return $api.call("chat/star_messages", params).then(function(res) {
                var prev_date, user_id;
                if (res.data.err_code === 0) {
                    messages = res.data.messages;
                    messages.forEach(function(cmsg) {
                        cmsg.content = cmsg.content + '';
                        cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg";
                        cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null);
                        cmsg.star = true;
                        if ($rootScope.cur_home.home_id !== cmsg.home_id) {
                            cmsg.mission_name = cmsg.mission_name + "(" + cmsg.home_name + ")";
                        }
                    });
                    return messages;
                } else {
                    return [];
                }
            });
        };
        read_messages = function(mission_id, cmsg_ids) {
            var params;
            params = {
                mission_id: mission_id,
                cmsg_ids: cmsg_ids
            };
            return $api.call("chat/read_messages", params).then(function(res) {
                return res.data;
            });
        };
        unread_messages = function(mission_id, cmsg_ids) {
            var params;
            params = {
                mission_id: mission_id,
                cmsg_ids: cmsg_ids
            };
            return $api.call("chat/unread_messages", params).then(function(res) {
                return res.data;
            });
        };

        delete_message = function(messages, cid) {
            var i, j, message, ref;

            if ($api.is_empty(messages)) {
                return;
            }

            i = 0;

            for (i = j = ref = messages.length - 1; j >= 0; i = j += -1) {
                message = messages[i];
                if (message.cmsg_id === cid) {
                    messages.splice(i, 1);
                    break;
                }
            }

            return;
        };

        set_message = function(mission_id, messages, cmsg, callback) {
            var i, j, l_cmsg, prev_message, ref, setted;
            cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg";
            cmsg.unread = $session.user_id !== cmsg.user_id;
            cmsg.read_class = (cmsg.unread ? "unread" : "read");
            if (cmsg.cmsg_id < 0) {
                if (messages.length > 0) {
                    l_cmsg = messages[messages.length - 1];
                    if (l_cmsg.user_id === cmsg.user_id) {
                        cmsg.show_avartar = false;
                    } else {
                        cmsg.show_avartar = true;
                    }
                } else {
                    cmsg.show_avartar = true;
                }
                messages.push(cmsg);
                if (callback)
                    callback(cmsg);
            } else {
                setted = false;
                if (cmsg.temp_cmsg_id !== null && cmsg.temp_cmsg_id !== void 0) {
                    delete_message(messages, cmsg.cmsg_id);
                }
                if (messages.length > 0) {
                    for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                        if (messages[i].cmsg_id === cmsg.temp_cmsg_id) {
                            messages[i].cmsg_id = cmsg.cmsg_id;
                        }
                        if (messages[i].cmsg_id === cmsg.cmsg_id) {
                            cmsg.inserted = false;
                            messages[i] = cmsg;
                            setted = true;
                            if (i === 0) {
                                cmsg.show_avartar = true;
                                cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null);
                            } else {
                                prev_message = messages[i - 1];
                                if (prev_message.user_id !== cmsg.user_id) {
                                    cmsg.show_avartar = true;
                                }
                                cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, prev_message.date);
                            }
                            setted = true;

                            if (callback)
                                callback(cmsg);
                            break;
                        }
                    }
                }
                if (cmsg.inserted && setted === false) {
                    if (messages.length > 0) {
                        l_cmsg = messages[messages.length - 1];
                        if (l_cmsg.user_id === cmsg.user_id) {
                            cmsg.show_avartar = false;
                            cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, l_cmsg.date);
                        } else {
                            cmsg.show_avartar = true;
                            cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null);
                        }
                    } else {
                        cmsg.show_avartar = true;
                        cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, null);
                    }
                    messages.push(cmsg);
                    if (callback)
                        callback(cmsg);
                }
            }

            cache_messages(mission_id, messages);
        };
        remove_message = function(messages, cmsg) {
            var i, j, message, next_message, prev_message, ref;
            for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                message = messages[i];
                if (message.cmsg_id === cmsg.cmsg_id) {
                    if (i < messages.length - 1) {
                        next_message = messages[i + 1];
                        if (message.show_avartar) {
                            next_message.show_avartar = true;
                        }
                        if (i > 0)
                        {
                            prev_message = messages[i - 1];
                            next_message.date_label = $dateutil.ellipsis_time_str(next_message.date, prev_message.date);
                        } else {
                            next_message.date_label = $dateutil.ellipsis_time_str(next_message.date, null);
                        }
                    }
                    messages.splice(i, 1);
                    break;
                }
            }
        };
        star_message = function(cmsg_id, star) {
            var params;
            params = {
                cmsg_id: cmsg_id,
                star: star
            };
            return $api.call("chat/star_message", params).then(function(res) {
                return res.data;
            });
        };
        upload_file = function(fileTransferMode, mission_id, file) {
            if (fileTransferMode) {
                return $api.upload_file2('mission/upload_attach', file, {
                    mission_id: mission_id
                });
            }
            else {
                return $api.upload_file('mission/upload_attach', file, {
                    mission_id: mission_id
                });
            }
        };
        cancel_upload_file = function(file) {
            $api.cancel_upload(file.upload);
            file.canceled = true;
        };

        get_unread = function(cmsg) {
            mission_id = cmsg.mission_id;
            unreads = $rootScope.unreads[mission_id];
            if (unreads) {
                for (i=0; i<unreads.length; i++) {
                    if (u.cmsg_id == cmsg.cmsg_id)
                        return u;
                }
            }

            return null;
        };

        set_unread = function(cmsg) {
            unread = get_unread(cmsg);
            if (unread)
                unread.to_flag = cmsg.to_flag;
            else {
                mission_id = cmsg.mission_id;
                unreads = $rootScope.unreads[mission_id];
                if (unreads == undefined) {
                    $rootScope.unreads[mission_id] = [];
                    unreads = $rootScope.unreads[mission_id];
                }
                unreads.push({
                    cmsg_id: cmsg.cmsg_id,
                    to_flag: cmsg.to_flag
                });
            }
        };

        remove_unread = function(cmsg) {
            mission_id = cmsg.mission_id;
            unreads = $rootScope.unreads[mission_id];
            if (unreads) {
                for (i=0; i<unreads.length; i ++) {
                    if (u.cmsg_id == cmsg.cmsg_id) {
                        unreads.splice(i, 1);
                        break;
                    }
                }
            }
        };

        refresh_unreads_title = function(refresh_home) {
            if (refresh_home === void 0) {
                refresh_home = true;
            }

            unreads = 0;
            to_unreads = 0;

            angular.forEach($rootScope.missions, function(mission) {
                if (mission.unreads > 0) {
                    unreads += mission.unreads;
                }
                if (mission.to_unreads > 0) {
                    to_unreads += mission.to_unreads;
                }
            });

            if ($rootScope.homes) {
                t_unreads = 0;
                t_to_unreads = 0;
                ref = $rootScope.homes;
                for (i = 0, len = ref.length; i < len; i++) {
                    home = ref[i];
                    if ($rootScope.cur_home && home.home_id === $rootScope.cur_home.home_id) {
                        home.unreads = unreads;
                        home.to_unreads = to_unreads;
                        if (refresh_home) {
                            $rootScope.$broadcast('refresh-home', home);
                        }
                    }
                    t_unreads += home.unreads;
                    t_to_unreads += home.to_unreads;
                }
            }

            title = "";

            if (t_unreads > 0) {
                title = "[" + t_unreads;
                if (t_to_unreads > 0) {
                    title += "(" + t_to_unreads + ")";
                }
                title += "]";
            }

            document.title = title + "ハンドクラウド";
            return;
        };

        reorder_home_mission = function(last_home_id, last_mission_id) {
            if (last_home_id) {
                order = 0;
                i = 0
                for (i = 0; i < $rootScope.homes.length; i ++) {
                    if ($rootScope.homes[i].order < order && $rootScope.homes[i].home_id != last_home_id)
                        order = $rootScope.homes[i].order;
                }
                for (i = 0; i < $rootScope.homes.length; i ++) {
                    if ($rootScope.homes[i].home_id == last_home_id)
                        $rootScope.homes[i].order = order - 1;
                }
                $rootScope.homes.sort(function(a, b) { return a.order - b.order;});
            }

            if (last_mission_id) {
                order = 0;
                i = 0
                for (i = 0; i < $rootScope.missions.length; i ++) {
                    if ($rootScope.missions[i].order < order && $rootScope.missions[i].mission_id != last_mission_id)
                        order = $rootScope.missions[i].order;
                }
                for (i = 0; i < $rootScope.missions.length; i ++) {
                    if ($rootScope.missions[i].mission_id == last_mission_id)
                        $rootScope.missions[i].order = order - 1;
                }

                $rootScope.missions.sort(function(a, b) { return a.order - b.order;});
            }

            refresh_unreads_title()
        };

        sound_alert = function() {
            var audioElement;
            audioElement = document.createElement('audio');
            if (navigator.userAgent.match('Firefox/')) {
                audioElement.setAttribute('src', 'sound/alert.ogg');
            } else {
                audioElement.setAttribute('src', 'sound/alert.mp3');
            }
            $.get();
            audioElement.addEventListener("load", function() {
                return audioElement.play();
            }, true);
            audioElement.pause();
            return audioElement.play();
        };
        search_read = function() {
            var params;
            params = {};
            params.offset = $rootScope.read_message_offset;
            return params.limit = 10;
        };

        refresh_badge = function() {
            unreads = 0;
            angular.forEach($rootScope.homes, function(home) {
                unreads += home.unreads;
            });
            try {
                if (ionic.Platform.isIOS())
                {
                    cordova.plugins.notification.badge.set(unreads);
                }
            }
            catch(e) {

            }
        };

        return {
            save_cache_messages_to_storage: save_cache_messages_to_storage,
            cache_messages: cache_messages,
            is_to_mine: is_to_mine,
            message_to_html: message_to_html,
            messages_to_html: messages_to_html,
            messages: messages,
            search_messages: search_messages,
            star_messages: star_messages,
            read_messages: read_messages,
            unread_messages: unread_messages,
            set_message: set_message,
            remove_message: remove_message,
            star_message: star_message,
            upload_file: upload_file,
            cancel_upload_file: cancel_upload_file,
            get_unread: get_unread,
            set_unread: set_unread,
            remove_unread: remove_unread,
            refresh_unreads_title: refresh_unreads_title,
            reorder_home_mission: reorder_home_mission,
            sound_alert: sound_alert,
            search_read: search_read,
            refresh_badge: refresh_badge
        };
    }
);