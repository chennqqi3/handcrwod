angular.module('app.service.chat', [])

.service('$chat', 
    function($rootScope, $session, chatStorage, userStorage, $http, CONFIG, 
        logger, $state, $timeout, $interval, AUTH_EVENTS, $websocket, $api, chatizeService, $dateutil) {
        var $this;
        $this = this;
        $this.socket = null;
        $this.out_queue = [];
        $this.out_key = 0;
        $this.client_key = Math.floor((Math.random() * 1000000) + 1);

        $this.connect = function() {
            var uri;
            if ($session.user_id === null) {
                return;
            }
            uri = $rootScope.chat_uri + $session.user_id + "/" + $this.client_key;
            $this.socket = $websocket.$new(uri);
            $this.socket.$$config.enqueue = true;
            if ($this.socket.$$config.reconnect != true) {
                console.log("Open connection");
                $this.socket.$open();
            }
            $this.socket.$on('$open', function() {
                console.log('Connected chat server');
                $rootScope.error_disconnected = false;
                $rootScope.$apply();
            });
            $this.socket.$on('$error', function(ev) {
                console.log('Error Occurred ' + ev.data);
                //logger.logError("メッセージ送信が失敗しました。");
            });
            $this.socket.$on('$close', function() {
                console.log('Connection closed');
                $timeout(function() {
                    $rootScope.error_disconnected = !($this.socket && $this.socket.$ready());
                    $rootScope.$apply();
                }, 3000);
            });
            $this.socket.$on('chat_message', function(cmsg) {
                found_home = false;
                found_mission_name　= null;
                self_message = false;
                sound = false;

                if ($session.user_id != cmsg.user_id) {
                    unread = chatStorage.get_unread(cmsg);

                    delta_unreads = 0;
                    delta_to_unreads = 0;

                    cmsg.to_flag = chatStorage.is_to_mine(cmsg);
                    if (!unread)
                        delta_unreads = 1;
                    if (!unread || unread && !unread.to_flag)
                        if (cmsg.to_flag)
                            delta_to_unreads = 1;

                    if ($rootScope.cur_home.home_id == cmsg.home_id && $rootScope.missions)
                    {
                        angular.forEach($rootScope.missions, function(mission) {
                            if (mission.mission_id == cmsg.mission_id) {
                                found_mission_name　= mission.mission_name;
                                mission.unreads += delta_unreads;
                                mission.to_unreads += delta_to_unreads;
                                if (delta_unreads > 0 || delta_to_unreads > 0) {
                                    chatStorage.set_unread(cmsg);
                                }
                                mission.visible = true;
                                $rootScope.$broadcast('unread-message', mission);
                            }
                        });
                    }

                    if (delta_unreads > 0 && $rootScope.homes) {
                        angular.forEach($rootScope.homes, function(home) {
                            if (home.home_id == cmsg.home_id) {
                                found_home = true;
                                home.unreads += delta_unreads;
                                home.to_unreads += delta_to_unreads;
                                $rootScope.$apply();
                                sound = true;
                                $rootScope.$broadcast('refresh-home', home);
                            }
                        });
                    }
                }
                else {
                    found_home = true;
                    self_message = true;
                }
                
                if (sound) {
                    chatStorage.sound_alert();
                    console.log('sound');
                }

                chatStorage.reorder_home_mission(cmsg.home_id, cmsg.mission_id);

                if (!found_home) {
                    $rootScope.$broadcast('refresh-homes');
                }

                if (!self_message) {
                    if (found_mission_name)
                    {
                        if ($state.current.name == 'tab.chatroom' && $rootScope.cur_mission != null 
                            && $rootScope.cur_mission.mission_id != cmsg.mission_id ||
                            $state.current.name != 'tab.chats' && $state.current.name != 'tab.chatroom') {
                            logger.logSuccess('「' + found_mission_name　+ '」からメッセージが届きました。');
                        }
                    }
                    else {
                        logger.logSuccess('他のグループからメッセージが届きました。');
                    }
                }
                console.log('receive_message temp_cmsg_id:' + cmsg.temp_cmsg_id + ' cmsg_id:' + cmsg.cmsg_id);
                $rootScope.$broadcast('receive_message', cmsg);

                chatStorage.refresh_badge();
            });

            $this.socket.$on('react_message', function(cmsg) {
                if ($rootScope.cur_mission.mission_id == cmsg.mission_id) {
                    return $rootScope.$broadcast('react_message', cmsg);
                }
            });
            
            $this.socket.$on('chat_messages', function(cmsg) {
                messages = cmsg.messages;
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
                cmsg.messages = messages;
                $rootScope.read_message_offset = 0;
                chatStorage.refresh_unreads_title();
                return $rootScope.$broadcast('receive_messages', cmsg);
            });
            $this.socket.$on('remove_message', function(cmsg) {
                $rootScope.$broadcast('refresh-homes');
                console.log('remove_message cmsg_id:' + cmsg.cmsg_id);
                return $rootScope.$broadcast('remove_message', cmsg);
            });

            $this.socket.$on('alert', function(cmsg) {
                $rootScope.$broadcast('alert', cmsg);
                $rootScope.$apply(function() {
                    return userStorage.alerts();
                });
            });
            $this.socket.$on('task', function(msg) {
                if ($rootScope.cur_mission !== null && $rootScope.cur_mission.mission_id === msg.mission_id) {
                    return $rootScope.$broadcast('refresh-tasks', msg);
                }
            });
            $this.socket.$on('mission', function(msg) {
                if ($rootScope.cur_home !== null && $rootScope.cur_home.home_id === msg.home_id) {
                    return $rootScope.$broadcast('refresh-missions');
                }
            });
            $this.socket.$on('home', function(msg) {
                if (msg.type === "refresh-logo") {
                    $rootScope.$broadcast('refresh-home-logo', msg.home_id);
                    return;
                }
                if ($rootScope.cur_home !== null && $rootScope.cur_home.home_id === msg.home_id) {
                    if (msg.type === "remove_member" || msg.type === "remove") {
                        logger.logSuccess("グループから削除されました。");
                        return $rootScope.$broadcast('removed_home');
                    } else if (msg.type === "accept_invite") {
                        return $rootScope.$broadcast('refresh-missions');
                    } else {
                        return $rootScope.$broadcast('refresh-homes', msg);
                    }
                } else {
                    return $rootScope.$broadcast('refresh-homes', msg);
                }
            });

            $this.socket.$on('ok', function(msg) {
                for (i = 0; i < $this.out_queue.length; i ++) {
                    m = $this.out_queue[i];
                    if (m.key == msg.key) {
                        $this.out_queue.splice(i, 1);
                        break;
                    }
                }
            });
            return;
        };
        $this.disconnect = function() {
            if ($this.socket !== null) {
                console.log("Close socket.")
                $this.socket.$un('$open');
                $this.socket.$un('$error');
                $this.socket.$un('$close');
                $this.socket.$un('chat_message');
                $this.socket.$un('remove_message');
                $this.socket.$close();
                return $this.socket = null;
            }
        };
        $this.retryConnect = function() {
            return $timeout(function() {
                return $this.connect();
            }, 3000);
        };
        $this.onLogin = function() {
            if ($this.socket !== null) {
                $this.disconnect();
            }
            return $this.connect();
        };
        $this.send = function(cmsg_id, home_id, mission_id, content, to_id, is_file) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    cmsg_id: cmsg_id,
                    home_id: home_id,
                    mission_id: mission_id,
                    content: content,
                    to_id: to_id,
                    is_file: is_file,
                    home_name: $rootScope.cur_home.home_name
                };
                $this.emit('chat_message', msg);
            }
        };
        $this.react = function(cmsg_id, mission_id, emoticon_id) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    cmsg_id: cmsg_id,
                    mission_id: mission_id,
                    emoticon_id: emoticon_id
                };
                return $this.emit('react_message', msg);
            }
        };
        $this.messages = function(mission_id, prev_id, next_id, star) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    home_id: $rootScope.cur_home.home_id,
                    mission_id: mission_id,
                    prev_id: prev_id,
                    next_id: next_id,
                    star: star,
                    limit: 200
                };
                $this.emit('chat_messages', msg);
            }
        };
        $this.remove = function(cmsg_id, mission_id) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    cmsg_id: cmsg_id,
                    mission_id: mission_id
                };
                $this.emit('remove_message', msg);
            }
        };
        $this.alert = function(alert_type, user_id, info) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    alert_type: alert_type,
                    user_id: user_id,
                    info: info
                };
                $this.emit('alert', msg);
            }
        };
        $this.task = function(type, task_id, mission_id) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    type: type,
                    task_id: task_id,
                    mission_id: mission_id
                };
                $this.emit('task', msg);
            }
        };
        $this.mission = function(type, mission_id, home_id) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    type: type,
                    mission_id: mission_id,
                    home_id: home_id
                };
                $this.emit('mission', msg);
            }
        };
        $this.home = function(type, home_id, user_id) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    type: type,
                    home_id: home_id,
                    user_id: user_id
                };
                $this.emit('home', msg);
            }
        };
        $this.bot_message = function(home_id) {
            var msg;
            if ($this.socket !== null) {
                msg = {
                    home_id: home_id
                };
                $this.emit('bot_message', msg);
            }
        };
        $this.set_status = function(status) {
          var msg;
            if ($this.socket !== null) {
                msg = {
                    status: status
                };
                $this.emit('status', msg);
            }  
        };

        $this.emit = function(evt, msg) {
            if (msg.key === undefined && evt != 'alive') {
                $this.out_key = $this.out_key + 1;
                msg.key = $this.out_key;
                msg.event = evt;
                msg.retry = 0;
                pushed = false;
                for (i = 0; i < $this.out_queue.length; i ++) {
                    m = $this.out_queue[i];
                    if (m.key == msg.key) { // check fail of send  
                        pushed = true;  
                    }
                }
                if (!pushed)
                    $this.out_queue.push(msg);
            }
            if ($this.socket) {
                $this.socket.$emit(evt, msg);
                $timeout(function() {
                    $this.resend(msg);
                }, 2000)
            }
        };

        $this.resend = function(msg) {
            for (i = 0; i < $this.out_queue.length; i ++) {
                m = $this.out_queue[i];
                if (m.key == msg.key) { // check fail of send
                    // resend
                    msg.retry ++;
                    console.log("resend messages " + msg.retry + " detail:" + JSON.stringify(msg));
                    $this.emit(m.event, m);
                    if (msg.retry > 3) {
                        $this.disconnect();
                        $this.connect();
                        
                        for (i = 0; i < $this.out_queue.length; i ++) {
                            $this.out_queue[i].retry = 0;
                        }
                        break;
                    }

                }
            }
        };

        $rootScope.$on('reload_session', $this.onLogin);
        $rootScope.$on(AUTH_EVENTS.loginSuccess, $this.onLogin);
        $rootScope.$on('closed_session', function() {
            if ($this.socket !== null) {
                return $this.disconnect();
            }
        });

        $interval(function() {
            if ($this.socket && $this.socket.$ready()) {
                msg = {
                    time: new Date().getTime()
                }
                console.log("send alive")
                $this.emit('alive', msg);
            }
        }, 30000);
        return $this;
    }
);