angular.module('app.storage.mission', [])

.factory('missionStorage', 
    function($rootScope, $api, $session, $dateutil, $chat, $filter) {
        var add, attaches, break_mission, complete, delete_back_image, edit, get, get_mission, invitable_members, invite, open, open_member, pin, refresh_remaining, refresh_sort, remove, remove_attach, remove_member, search, search_completed, set_back_pos, set_mission, set_repeat, unpinned_missions, priv;
        search = function(home_id) {
            var params;
            params = {
                home_id: home_id
            };
            return $api.call("mission/search", params).then(function(res) {
                var missions;
                if (res.data.err_code === 0) {
                    $rootScope.missions = reset_order(res.data.missions);
                    $rootScope.missions.sort(function(a, b) { return a.order - b.order;});
                    $rootScope.mission_complete_offset = 0;
                } else {
                    $rootScope.missions = [];
                }
                $rootScope.$broadcast('refreshed-missions');
                return $rootScope.missions;
            });
        };

        reset_order = function(missions) {
            order = 0
            missions.forEach(function(mission) {
                mission.order = order;
                order += 1;

                mission.complete_flag = mission.complete_flag == 1;
                if (mission.private_flag == 3)
                    $rootScope.bot_mission = mission;
            });
            return missions
        }

        unpinned_missions = function(home_id, private_flag, callback) {
            var $params;
            $params = {
                home_id: home_id,
                private_flag: private_flag
            };
            return $api.call("mission/unpinned_missions", $params).then(function(res) {
                var missions;
                if (res.data.err_code === 0) {
                    missions = res.data.missions;
                    missions.forEach(function(mission) {
                        return mission.complete_flag = mission.complete_flag === 1;
                    });
                }
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        search_completed = function() {
            var params;
            params = {
                offset: $rootScope.mission_complete_offset,
                complete_flag: 1,
                limit: 10
            };
            return $api.call("mission/search", params).then(function(res) {
                var missions;
                if (res.data.err_code === 0) {
                    missions = res.data.missions;
                    missions.forEach(function(mission) {
                        mission.complete_flag = mission.complete_flag === 1;
                        return $rootScope.missions.push(mission);
                    });
                    refresh_remaining();
                    $rootScope.mission_complete_offset = params.offset + missions.length;
                    return $rootScope.missions;
                } else {
                    return [];
                }
            });
        };
        refresh_remaining = function() {
            var sel_mission_id;
            if ($rootScope.cur_mission !== null) {
                sel_mission_id = $rootScope.cur_mission.mission_id;
            }
            if ($rootScope.missions !== void 0) {
                return $rootScope.missions.forEach(function(mission) {
                    var remaining;
                    if ($rootScope.tasks !== void 0) {
                        remaining = 0;
                        $rootScope.tasks.forEach(function(task) {
                            if (task.complete_flag === false && task.performer_id === $session.user_id && task.mission_id === mission.mission_id) {
                                return remaining += 1;
                            }
                        });
                        return mission.remainingTasks = remaining;
                    }
                });
            }
        };
        refresh_sort = function() {
            var sort;
            sort = 0;
            return $rootScope.missions.forEach(function(mission) {
                if (mission.complete_flag === false) {
                    mission.sort0 = sort;
                    mission.sort = sort;
                    return sort += 1;
                }
            });
        };
        get_mission = function(mission_id) {
            var j, len, mission, ref;
            ref = $rootScope.missions;
            for (j = 0, len = ref.length; j < len; j++) {
                mission = ref[j];
                if (mission.mission_id === mission_id) {
                    return mission;
                }
            }
            return null;
        };
        set_mission = function(mission) {
            var i, j, ref;
            if ($rootScope.missions !== null && $rootScope.missions.length > 0) {
                for (i = j = 0, ref = $rootScope.missions.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                    if ($rootScope.missions[i].mission_id === mission.mission_id) {
                        if ($rootScope.missions[i] != mission) {
                            $rootScope.missions[i] = mission;
                            $rootScope.missions = reset_order($rootScope.missions);   
                        }
                        return;
                    }
                }
            }
            $rootScope.missions.push(mission);
            $rootScope.missions = reset_order($rootScope.missions);
            return null;
        };
        set_cur_mission = function(mission, toStorage) {
            if (toStorage == undefined) {
                toStorage = true;
            }
            $rootScope.cur_mission = mission;
            if ($rootScope.cur_mission) {
                $rootScope.cur_mission.visible = true;
                set_mission($rootScope.cur_mission);
            }
            if (toStorage) {
                $session.statesToStorage();
            }
        };
        add = function(mission, callback) {
            return $api.call("mission/add", mission).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.mission('add', res.data.mission_id, res.data.home_id);
                    }
                }
            });
        };
        get_name = function(mission_id, callback) {
            var params = {
                mission_id: mission_id
            };
            return $api.call("mission/get_name", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        get = function(mission_id, callback) {
            var params;
            params = {
                mission_id: mission_id
            };
            return $api.call("mission/get", params).then(function(res) {
                if (res.data.err_code === 0) {
                    res.data.mission.complete_flag = res.data.mission.complete_flag === 1;
                    if (res.data.mission.emoticons) {
                        for (i = 0; i<res.data.mission.emoticons.length; i ++) {
                            icon = res.data.mission.emoticons[i];
                            $api.init_emoticon(icon);
                        }

                        $rootScope.emoticons = res.data.mission.emoticons;
                    }
                }
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        edit = function(mission, callback) {
            return $api.call("mission/edit", mission).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.mission('edit', res.data.mission_id, res.data.home_id);
                    }
                }
            });
        };
        open = function(mission_id, callback) {
            var params;
            params = {
                mission_id: mission_id
            };
            return $api.call("mission/open", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        pin = function(mission_id, pinned, callback) {
            var params;
            params = {
                mission_id: mission_id,
                pinned: pinned
            };
            return $api.call("mission/pin", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        attaches = function(mission_id, callback) {
            var params;
            params = {
                mission_id: mission_id
            };
            return $api.call("mission/attaches", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        remove = function(mission, callback) {
            var params;
            params = {
                mission_id: mission.mission_id
            };
            return $api.call("mission/remove", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.mission('remove', res.data.mission_id, res.data.home_id);
                    }
                }
            });
        };
        add_member_with_url = function(mission_id, user_id, invite_url, callback) {
            var params;
            params = {
                mission_id: mission_id,
                user_id: user_id,
                invite_url: invite_url
            };
            $api.call("mission/add_member", params).then(function(res) {
                if (callback != void 0) {
                    return callback(res.data);
                }
            });
        };
        open_member = function(home_id, user_id, callback) {
            var params;
            params = {
                home_id: home_id,
                user_id: user_id
            };
            return $api.call("mission/open", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        remove_member = function(mission_id, user_id, callback) {
            var params;
            params = {
                mission_id: mission_id,
                user_id: user_id
            };
            return $api.call("mission/remove_member", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.mission('remove_member', res.data.mission_id, res.data.home_id);
                    }
                }
            });
        };
        invitable_members = function(mission_id, callback) {
            var params;
            params = {
                mission_id: mission_id
            };
            return $api.call("mission/invitable_members", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        invite = function(req, callback) {
            return $api.call("mission/invite", req).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.mission('invite', res.data.mission_id, res.data.home_id);
                    }
                }
            });
        };
        self_invite = function(mission_id, invite_key, callback) {
            var req = {
                mission_id: mission_id,
                invite_key: invite_key
            }
            return $api.call("mission/self_invite", req).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        remove_attach = function(mission_id, mission_attach_id, callback) {
            var params;
            params = {
                mission_id: mission_id,
                mission_attach_id: mission_attach_id
            };
            return $api.call("mission/delete_attach", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        complete = function(mission_id, complete_flag, callback) {
            var params;
            params = {
                mission_ids: mission_id,
                complete_flag: complete_flag
            };
            return $api.call("mission/complete", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        break_mission = function(mission_id, callback) {
            var params;
            params = {
                mission_id: mission_id
            };
            return $api.call("mission/break_mission", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        set_repeat = function(mission_id, repeat_type, repeat_weekday, repeat_month, repeat_monthday, callback) {
            var params;
            if (repeat_weekday === void 0 || repeat_weekday < 0 || repeat_weekday > 6) {
                repeat_weekday = 1;
            }
            if (repeat_month === void 0 || repeat_month < 1 || repeat_month > 12) {
                repeat_month = 1;
            }
            if (repeat_monthday === void 0 || repeat_monthday < 1 || repeat_monthday > 31) {
                repeat_monthday = 1;
            }
            params = {
                mission_id: mission_id,
                repeat_type: repeat_type,
                repeat_weekday: repeat_weekday,
                repeat_month: repeat_month,
                repeat_monthday: repeat_monthday
            };
            $api.call("mission/set_repeat", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        set_back_pos = function(mission_id, type, back_pos, callback) {
            var params;
            params = {
                mission_id: mission_id,
                type: type,
                back_pos: back_pos
            };
            $api.call("mission/set_back_pos", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };

        upload_back_image = function(mission_id, type, file) {
            return $api.upload_file('mission/upload_back_image', file, {
                mission_id: mission_id,
                type: type
            });
        };

        delete_back_image = function(mission_id, type, callback) {
            var params;
            params = {
                mission_id: mission_id,
                type: type
            };
            $api.call("mission/delete_back_image", params).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };

        mission_html_id = function(mission) {
            if (mission.private_flag == 0 || mission.private_flag == 1) 
                return 'mission_' + mission.mission_id;
            else if (mission.private_flag == 2)
                return 'mission2_' + mission.user_id;
        };

        mission_unreads_to_html = function(mission) {
            if (mission.unreads > 0)
                return '       <i class="badge badge-danger">' + mission.unreads + '</i> ' + $filter('chatize')(mission.last_text, true);

            return '';
        };

        mission_to_html = function(mission, groups, include_self) {
            html = "";
            if (include_self == undefined)
                include_self = true;

            mission_id = mission.mission_id;
            if (mission.private_flag == 0 || mission.private_flag == 1) {
                /*
                <ion-item class="item-remove-animate item-icon-right item-icon-left item-accordion" ng-repeat="mission in missions | filter:roomFilter | orderBy:'order' track by mission.mission_id" type="item-text-wrap" ui-sref="tab.chatroom({mission_id:mission.mission_id})" ng-class="{'item-calm': nav_id=='chatroom_' + mission.mission_id, 'with-last-text': mission.last_text!='' && mission.last_text!=null}" ng-show="groups[0] || mission.visible">
                    <i class="icon icon-bubbles"></i>
                    <i class="icon-pin text-gray" ng-show="mission.pinned==1"></i>
                    <h2><i class="icon-pin text-gray" ng-show="mission.pinned==1"></i><i class="fa fa-lock" ng-if="mission.private_flag==1"></i>  {{mission.mission_name}}</h2>
                    <p><i class="badge badge-danger" ng-show="mission.unreads > 0">{{mission.unreads}}</i> <span ng-bind-html="mission.last_text | chatize:true"></span></p>
                    <i class="icon ion-chevron-right icon-accessory"></i>

                    <ion-option-button class="button-positive" ng-click="pinMission(mission)" ng-show="mission.pinned!=1">
                        <i class="icon icon-pin"></i>
                    </ion-option-button>
                    <ion-option-button class="button-assertive" ng-click="pinMission(mission)" ng-show="mission.pinned==1">
                        <i class="icon ion-ios-trash-outline"></i>
                    </ion-option-button>
                </ion-item>
                */

                item_class = ' ';
                if ($rootScope.nav_id == 'chatroom_' + mission.mission_id)
                    item_class += 'item-calm ';
                if (mission.last_text!='' && mission.last_text!=null)
                    item_class += 'with-last-text ';
                if (!(groups[0] || mission.visible))
                    item_class += 'hide';

                pin_show = ' ';
                unpin_show = ' ';
                if (mission.pinned == 1)
                    unpin_show = ' hide';
                else
                    pin_show = ' hide';

                private_show = ' hide';
                if (mission.private_flag == 1)
                    private_show = '';

                if (include_self)
                    html += '<ion-item id="' + mission_html_id(mission) + '" class="item-remove-animate item-icon-right item-icon-left item-accordion' + item_class + '" type="item-text-wrap" ui-sref="tab.chatroom({mission_id:' + mission_id + '})">';
                html += '    <i class="icon icon-bubbles"></i>';
                html += '    <i class="pin icon-pin text-gray' + pin_show + '"></i>';
                html += '    <h2><i class="fa fa-lock' + private_show + '"></i>  ' + mission.mission_name + '</h2>';
                html += '    <p class="unreads">' + mission_unreads_to_html(mission) + '</p>';
                html += '    <i class="icon ion-chevron-right icon-accessory"></i>';
                html += '    <ion-option-button class="btn-pin button-positive' + unpin_show + '" ng-click="pinMission(' + mission_id + ')">';
                html += '        <i class="icon icon-pin"></i>';
                html += '    </ion-option-button>';
                html += '    <ion-option-button class="btn-unpin button-assertive' + pin_show + '" ng-click="pinMission(' + mission_id + ')">';
                html += '        <i class="icon ion-ios-trash-outline"></i>';
                html += '    </ion-option-button>';
                if (mission.private_flag == 1) {
                    html += '    <ion-option-button ng-click="breakMission(' + mission_id + ')">';
                    html += '        <i class="icon ion-log-out"></i>';
                    html += '    </ion-option-button>';
                }
                if (include_self)
                    html += '</ion-item>';
            }
            else if (mission.private_flag == 2) {
                /*
                <ion-item class="item-remove-animate item-icon-right item-avatar item-accordion" ng-repeat="mission in missions | filter:memberFilter | orderBy:'order' track by mission.user_id" type="item-text-wrap" ng-click="open_member(mission)" ng-class="{'item-calm': nav_id=='chatroom_' + mission.mission_id, 'with-last-text': mission.last_text!='' && mission.last_text!=null}" ng-show="groups[2] || mission.visible">
                    <img ng-src="{{mission.avartar}}" class="avatar">
                    <h2><i class="icon-pin text-gray" ng-show="mission.pinned==1"></i> {{mission.mission_name}}</h2>
                    <p><i class="badge badge-danger" ng-show="mission.unreads > 0">{{mission.unreads}}</i> <span ng-bind-html="mission.last_text | chatize:true"></span></p>
                    <i class="icon ion-chevron-right icon-accessory"></i>

                    <ion-option-button class="button-positive" ng-click="pinMission(mission)" ng-show="mission.pinned!=1">
                        <i class="icon icon-pin"></i>
                    </ion-option-button>
                    <ion-option-button class="button-assertive" ng-click="pinMission(mission)" ng-show="mission.pinned==1">
                        <i class="icon ion-ios-trash-outline"></i>
                    </ion-option-button>
                </ion-item>
                */
                item_class = ' ';
                if ($rootScope.nav_id == 'chatroom_' + mission.mission_id)
                    item_class += 'item-calm ';
                if (mission.last_text!='' && mission.last_text!=null)
                    item_class += 'with-last-text ';
                if (!(groups[2] || mission.visible))
                    item_class += 'hide';

                pin_show = ' ';
                unpin_show = ' ';
                if (mission.pinned == 1)
                    unpin_show = ' hide';
                else
                    pin_show = ' hide';

                private_show = ' hide';
                if (mission.private_flag == 1)
                    private_show = '';

                if (include_self)
                    html += '<ion-item id="' + mission_html_id(mission) + '" class="item-remove-animate item-avatar item-icon-right item-accordion' + item_class + '" type="item-text-wrap" ng-click="open_member(' + mission.user_id + ')">';
                html += '    <img ng-src="' + mission.avartar + '" class="avatar">';
                html += '    <h2><i class="pin icon-pin text-gray' + pin_show + '"></i>  ' + mission.mission_name + '</h2>';
                html += '    <p class="unreads">' + mission_unreads_to_html(mission) + '</p>';
                html += '    <i class="icon ion-chevron-right icon-accessory"></i>';
                html += '    <ion-option-button class="btn-pin button-positive' + unpin_show + '" ng-click="pinMission(' + mission_id + ')">';
                html += '        <i class="icon icon-pin"></i>';
                html += '    </ion-option-button>';
                html += '    <ion-option-button class="btn-unpin button-assertive' + pin_show + '" ng-click="pinMission(' + mission_id + ')">';
                html += '        <i class="icon ion-ios-trash-outline"></i>';
                html += '    </ion-option-button>';
                html += '    <ion-option-button ng-click="breakMission(' + mission_id + ')">';
                html += '        <i class="icon ion-log-out"></i>';
                html += '    </ion-option-button>';
                if (include_self)
                    html += '</ion-item>';

            }

            return html;
        };

        priv = function(mission_id, user_id, priv, callback) {
            params = {
                mission_id: mission_id,
                user_id: user_id,
                priv: priv
            }

            $api.call("mission/priv", params)
                .then(function(res) {
                    if (callback != undefined)
                        callback(res.data);
                });
        };

        upload_emoticon = function(mission_id, file) {
            return $api.upload_file('mission/upload_emoticon', file, {
                mission_id: mission_id
            });
        };

        add_emoticon = function(emoticon, callback) {
            return $api.call("mission/add_emoticon", emoticon).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };

        return {
            search: search,
            unpinned_missions: unpinned_missions,
            search_completed: search_completed,
            refresh_remaining: refresh_remaining,
            get_mission: get_mission,
            set_mission: set_mission,
            set_cur_mission: set_cur_mission,
            add: add,
            get_mission: get_mission,
            get: get,
            get_name: get_name,
            edit: edit,
            open: open,
            pin: pin,
            attaches: attaches,
            remove: remove,

            add_member_with_url: add_member_with_url,

            open_member: open_member,
            remove_member: remove_member,
            invitable_members: invitable_members,
            invite: invite,
            self_invite: self_invite,

            remove_attach: remove_attach,
            complete: complete,
            break_mission: break_mission,
            set_repeat: set_repeat,
            
            set_back_pos: set_back_pos,
            upload_back_image: upload_back_image,
            delete_back_image: delete_back_image,

            mission_html_id: mission_html_id,
            mission_unreads_to_html: mission_unreads_to_html,
            mission_to_html: mission_to_html,
            
            priv: priv,

            upload_emoticon: upload_emoticon,
            add_emoticon: add_emoticon
        };
    }
);