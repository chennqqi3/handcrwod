var g_messages = null;

angular.module('app.storage.chat', [])

.factory('$emoticons', function() {
    var icons;
    icons = [
        {
            "class": 'emoticon-smile',
            title: '笑顔',
            alt: ':)'
        }, {
            "class": 'emoticon-sad',
            title: '悲しい',
            alt: ':('
        }, {
            "class": 'emoticon-more-smile',
            title: 'もっとスマイル',
            alt: ':D'
        }, {
            "class": 'emoticon-lucky',
            title: 'やったね',
            alt: '8-)'
        }, {
            "class": 'emoticon-surprise',
            title: 'びっくり',
            alt: ':o'
        }, {
            "class": 'emoticon-wink',
            title: 'ウィンク',
            alt: ';)'
        }, {
            "class": 'emoticon-tears',
            title: 'ウェ～ん',
            alt: ';('
        }, {
            "class": 'emoticon-sweat',
            title: '汗',
            alt: '(sweat)'
        }, {
            "class": 'emoticon-mumu',
            title: 'むむ',
            alt: ':|'
        }, {
            "class": 'emoticon-kiss',
            title: 'チュ！',
            alt: ':*'
        }, {
            "class": 'emoticon-tongueout',
            title: 'べー',
            alt: ':p'
        }, {
            "class": 'emoticon-blush',
            title: '恥ずかしい',
            alt: '(blush)'
        }, {
            "class": 'emoticon-wonder',
            title: '何なに',
            alt: ':^)'
        }, {
            "class": 'emoticon-snooze',
            title: '眠い',
            alt: '|-)'
        }, {
            "class": 'emoticon-love',
            title: '恋してます',
            alt: '(inlove)'
        }, {
            "class": 'emoticon-grin',
            title: 'ニヤッ',
            alt: ']:)'
        }, {
            "class": 'emoticon-talk',
            title: '話す',
            alt: '(talk)'
        }, {
            "class": 'emoticon-yawn',
            title: 'あくび',
            alt: '(yawn)'
        }, {
            "class": 'emoticon-puke',
            title: 'ゲーッ',
            alt: '(puke)'
        }, {
            "class": 'emoticon-ikemen',
            title: 'イケメン',
            alt: '(emo)'
        }, {
            "class": 'emoticon-otaku',
            title: 'オタク',
            alt: '8-|'
        }, {
            "class": 'emoticon-ninmari',
            title: 'ニンマリ',
            alt: ':#)'
        }, {
            "class": 'emoticon-nod',
            title: 'うんうん',
            alt: '(nod)'
        }, {
            "class": 'emoticon-shake',
            title: 'いやいや',
            alt: '(shake)'
        }, {
            "class": 'emoticon-wry-smile',
            title: '苦笑い',
            alt: '(^^;)'
        }, {
            "class": 'emoticon-whew',
            title: 'やれやれ',
            alt: '(whew)'
        }, {
            "class": 'emoticon-clap',
            title: '拍手',
            alt: '(clap)'
        }, {
            "class": 'emoticon-bow',
            title: 'おじぎ',
            alt: '(bow)'
        }, {
            "class": 'emoticon-roger',
            title: '了解！',
            alt: '(roger)'
        }, {
            "class": 'emoticon-muscle',
            title: '筋肉モリモリ',
            alt: '(flex)'
        }, {
            "class": 'emoticon-dance',
            title: 'ダンス',
            alt: '(dance)'
        }, {
            "class": 'emoticon-komanechi',
            title: 'コマネチ',
            alt: '(:/)'
        }, {
            "class": 'emoticon-devil',
            title: '悪魔',
            alt: '(devil)'
        }, {
            "class": 'emoticon-star',
            title: '星',
            alt: '(*)'
        }, {
            "class": 'emoticon-heart',
            title: 'ハート',
            alt: '(h)'
        }, {
            "class": 'emoticon-flower',
            title: '花',
            alt: '(F)'
        }, {
            "class": 'emoticon-cracker',
            title: 'クラッカー',
            alt: '(cracker)'
        }, {
            "class": 'emoticon-cake',
            title: 'ケーキ',
            alt: '(^)'
        }, {
            "class": 'emoticon-coffee',
            title: 'コーヒー',
            alt: '(coffee)'
        }, {
            "class": 'emoticon-beer',
            title: 'ビール',
            alt: '(beer)'
        }, {
            "class": 'emoticon-handshake',
            title: '握手',
            alt: '(handshake)'
        }, {
            "class": 'emoticon-yes',
            title: 'はい',
            alt: '(y)'
        }
    ];

    for(i = 0; i < icons.length; i ++)
    {
        icon = icons[i]
        icon.exp = icon.alt.replace(/\)/g, '\\)')
        icon.exp = icon.exp.replace(/\(/g, '\\(')
        icon.exp = icon.exp.replace(/\:/g, '\\:')
        icon.exp = icon.exp.replace(/\|/g, '\\|')
        icon.exp = icon.exp.replace(/\*/g, '\\*')
        icon.exp = icon.exp.replace(/\^/g, '\\^')
        icon.exp = new RegExp(icon.exp, 'g')
    }
    return {
        icons: icons
    };
})

.factory('chatStorage', 
    function($api, $session, $dateutil, $rootScope, filterFilter, AUTH_EVENTS, $auth, CONFIG) {
        var cancel_upload_file, init, messages, read_messages, refresh_unreads_title, remove_message, search_messages, search_read, set_message, sound_alert, star_message, upload_file;
        init = function() {

            /*
            if $auth.isAuthenticated()
                    search()
             */
        };

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
                // write
                g_messages["m_" + mission_id] = messages;
                console.log("message length " + g_messages["m_" + mission_id].length + " write m_" + mission_id);
            }

            return messages;
        }

        messages = function(mission_id, prev_id, next_id, star) {
            var params;
            params = {
                home_id: $rootScope.cur_home.home_id,
                mission_id: mission_id,
                prev_id: prev_id,
                next_id: next_id,
                star: star
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
                            return user_id = cmsg.user_id;
                        } else {
                            return cmsg.show_avartar = false;
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
                    user_id = null;
                    prev_date = null;
                    mission_id = null;
                    messages.forEach(function(cmsg) {
                        var date_label;
                        cmsg.avartar = CONFIG.AVARTAR_URL + cmsg.user_id + ".jpg";
                        date_label = $dateutil.ellipsis_time_str(cmsg.date, prev_date);
                        prev_date = cmsg.date;
                        cmsg.date_label = date_label;
                        if (cmsg.mission_id !== mission_id || cmsg.user_id !== user_id) {
                            cmsg.show_avartar = true;
                            user_id = cmsg.user_id;
                            mission_id = cmsg.mission_id;
                        } else {
                            cmsg.show_avartar = false;
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
        set_message = function(mission_id, messages, cmsg) {
            var i, j, l_cmsg, prev_message, ref;
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
            } else {
                setted = false;
                if (messages.length > 0) {                
                    for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                        if (messages[i].cmsg_id == cmsg.temp_cmsg_id || messages[i].cmsg_id === cmsg.cmsg_id) {
                            cmsg.inserted = false; // from self 
                            messages[i] = cmsg;
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
                            break;
                        }
                    }
                }

                if (cmsg.inserted && setted == false) { // from other user
                    if (messages.length > 0) {
                        l_cmsg = messages[messages.length - 1];
                        if (l_cmsg.user_id == cmsg.user_id)
                            cmsg.show_avartar = false;
                        else
                            cmsg.show_avartar = true;
                    }
                    else {
                        cmsg.show_avartar = true;
                    }
                    messages.push(cmsg);
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
        };
        refresh_unreads_title = function() {
            var title, unread_missions;
            unread_missions = 0;
            unreads = 0;
            angular.forEach($rootScope.missions, function(mission) {
                if (mission.unreads > 0) {
                    unreads += mission.unreads;
                }
            });

            if ($rootScope.homes) {
                for (var i =0; i < $rootScope.homes.length; i ++)
                {
                    if ($rootScope.homes[i].home_id == $rootScope.cur_home.home_id) {
                        $rootScope.homes[i].unreads = unreads
                    }
                    unread_missions+=$rootScope.homes[i].unreads;
                }
            }
            title = "";
            try {
                if (unread_missions > 0) {
                    title = "[" + unread_missions + "]";
                    cordova.plugins.notification.badge.set(unread_missions);
                    $rootScope.unread_missions = unread_missions;
                }
                else {
                    cordova.plugins.notification.badge.clear();
                    $rootScope.unread_missions = null;
                }
            }
            catch(err) {
                
            }
            document.title = title + "ハンドクラウド";
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
        return {
            init: init,
            save_cache_messages_to_storage: save_cache_messages_to_storage,
            cache_messages: cache_messages,
            messages: messages,
            search_messages: search_messages,
            read_messages: read_messages,
            set_message: set_message,
            remove_message: remove_message,
            star_message: star_message,
            upload_file: upload_file,
            cancel_upload_file: cancel_upload_file,
            refresh_unreads_title: refresh_unreads_title,
            reorder_home_mission: reorder_home_mission,
            sound_alert: sound_alert,
            search_read: search_read
        };
    }
);