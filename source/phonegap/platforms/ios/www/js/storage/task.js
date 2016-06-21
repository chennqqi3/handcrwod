'use strict';
angular.module('app.storage.task', [])

.factory('taskStorage', 
    function($rootScope, $api, $session, $dateutil, filterFilter, missionStorage, AUTH_EVENTS, $auth, $chat) {
        var add, add_comment, add_proclink, all_skills, edit, get_candidates, get_comments, get_completed_offset, get_proclinks, get_skills, get_task, get_taskcount, help_entrance, init, isEqual, isEqualLink, isEqualLinks, isEqualTasks, push_tasks, refresh_remaining, refresh_sort, remove, remove_comment, remove_proclink, request_entrance, search, search_completed, set_completed_offset, set_skills;
        init = function() {
            if ($auth.isAuthenticated()) {
                return search();
            }
        };
        search = function(mission_id) {
            var params;
            params = null;
            if (mission_id !== void 0) {
                params = {
                    mission_id: mission_id,
                    search_mode: 1
                };
            } else if ($rootScope.calendar_date !== null) {
                params = {
                    search_date: $rootScope.calendar_date,
                    search_mode: 1
                };
            }
            else if ($rootScope.cur_home != null) {
                params = {
                    home_id: $rootScope.cur_home.home_id
                };
            }

            return $api.call("task/search", params).then(function(res) {
                var tasks;
                if (res.data.err_code === 0) {
                    tasks = res.data.tasks;
                    tasks.forEach(function(task) {
                        task.priority = task.priority === 1;
                        task.complete_flag = task.complete_flag === 1;
                        return task.checked = false;
                    });
                    if (params === null) {
                        $rootScope.complete_offsets = {};
                    }
                    if (mission_id !== void 0) {
                        $rootScope.tasks = tasks;
                    } else {
                        $rootScope.tasks = tasks;
                        refresh_remaining();
                        refresh_sort();
                    }
                } else {
                    $rootScope.tasks = [];
                }
                $rootScope.$broadcast('refreshed-tasks');
                return $rootScope.tasks;
            });
        };
        get_proclinks = function(mission_id, callback) {
            $api.call("task/get_proclinks", {
                mission_id: mission_id
            }).then(function(res) {
                if (callback !== void 0) {
                    return callback(res.data);
                }
            });
        };
        get_completed_offset = function(mission_id) {
            var offset;
            offset = $rootScope.complete_offsets[mission_id];
            if (offset === void 0) {
                offset = 0;
            }
            return offset;
        };
        set_completed_offset = function(mission_id, offset) {
            return $rootScope.complete_offsets[mission_id] = offset;
        };
        search_completed = function() {
            var mission_id, offset_key, params;
            params = {};
            if ($rootScope.calendar_date !== null) {
                return;
            }
            if ($rootScope.cur_mission !== null) {
                mission_id = $rootScope.cur_mission.mission_id;
                params = {
                    mission_id: mission_id
                };
                offset_key = mission_id;
            } else {
                offset_key = 'all';
            }
            params.offset = get_completed_offset(offset_key);
            params.search_mode = 2;
            params.limit = 10;
            return $api.call("task/search", params).then(function(res) {
                var tasks;
                if (res.data.err_code === 0) {
                    tasks = res.data.tasks;
                    tasks.forEach(function(task) {
                        task.priority = task.priority === 1;
                        task.complete_flag = task.complete_flag === 1;
                        return task.checked = false;
                    });
                    set_completed_offset(offset_key, params.offset + tasks.length);
                    push_tasks(tasks);
                    return tasks;
                } else {
                    return [];
                }
            });
        };
        push_tasks = function(tasks) {
            var found, j, k, len, len1, ref, t, task;
            for (j = 0, len = tasks.length; j < len; j++) {
                task = tasks[j];
                found = false;
                ref = $rootScope.tasks;
                for (k = 0, len1 = ref.length; k < len1; k++) {
                    t = ref[k];
                    if (t.task_id === task.task_id) {
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    $rootScope.tasks.push(task);
                }
            }
        };
        refresh_sort = function() {
            var sort;
            sort = 0;
            return $rootScope.tasks.forEach(function(task) {
                if (task.complete_flag === false && task.processed === 0) {
                    task.sort0 = sort;
                    task.sort = sort;
                    return sort += 1;
                }
            });
        };
        refresh_remaining = function() {
            /*
            var remainingSel;
            $rootScope.priorityTasks = 0;
            $rootScope.remainingITasks = 0;
            $rootScope.remainingMTasks = 0;
            remainingSel = 0;
            $rootScope.tasks.forEach(function(task) {
                if (task.complete_flag === false && task.performer_id === $session.user_id) {
                    if (task.priority === true) {
                        $rootScope.priorityTasks += 1;
                    }
                    if (task.mission_id === null) {
                        $rootScope.remainingITasks += 1;
                    } else {
                        $rootScope.remainingMTasks += 1;
                    }
                    if ($rootScope.cur_mission !== null && task.mission_id === $rootScope.cur_mission.mission_id) {
                        return remainingSel += 1;
                    }
                }
            });
            if ($rootScope.cur_mission !== null) {
                $rootScope.remainingSelTasks = remainingSel;
            } else {
                $rootScope.remainingSelTasks = 0;
            }
            return missionStorage.refresh_remaining();
            */
        };
        get_task = function(task_id) {
            var j, len, ref, task;
            ref = $rootScope.tasks;
            for (j = 0, len = ref.length; j < len; j++) {
                task = ref[j];
                if (task.task_id === task_id) {
                    return task;
                }
            }
            return null;
        };
        get_taskcount = function(mission_id) {
            var count, j, len, ref, task;
            count = 0;
            ref = $rootScope.tasks;
            for (j = 0, len = ref.length; j < len; j++) {
                task = ref[j];
                if (task.mission_id === mission_id) {
                    count += 1;
                }
            }
            return count;
        };
        isEqualTasks = function(tasks1, tasks2) {
            var i, j, ref;
            if ($api.is_empty(tasks1) && $api.is_empty(tasks2)) {
                return true;
            }
            if (!$api.is_empty(tasks1) && !$api.is_empty(tasks2)) {
                if (tasks1.length !== tasks2.length) {
                    return false;
                }
                for (i = j = 0, ref = tasks1.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                    if (!isEqual(tasks1[i], tasks2[i])) {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        };
        isEqual = function(task1, task2) {
            if ($api.is_empty(task1) && $api.is_empty(task2)) {
                return true;
            }
            if (task1.task_id !== task2.task_id) {
                return false;
            }
            if (task1.task_name !== task2.task_name) {
                return false;
            }
            if (task1.progress !== task2.progress) {
                return false;
            }
            if (task1.avartar !== task2.avartar) {
                return false;
            }
            if (task1.complete_flag !== task2.complete_flag) {
                return false;
            }
            if (task1.complete_time !== task2.complete_time) {
                return false;
            }
            if (task1.plan_end_date !== task2.plan_end_date) {
                return false;
            }
            if (task1.plan_hours !== task2.plan_hours) {
                return false;
            }
            if (task1.x !== task2.x) {
                return false;
            }
            if (task1.y !== task2.y) {
                return false;
            }
            return true;
        };
        isEqualLinks = function(links1, links2) {
            var i, j, ref;
            if ($api.is_empty(links1) && $api.is_empty(links2)) {
                return true;
            }
            if (!$api.is_empty(links1) && !$api.is_empty(links2)) {
                if (links1.length !== links2.length) {
                    return false;
                }
                for (i = j = 0, ref = links1.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                    if (!isEqualLink(links1[i], links2[i])) {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        };
        isEqualLink = function(link1, link2) {
            if ($api.is_empty(link1) && $api.is_empty(link2)) {
                return true;
            }
            if (link1.from_task_id !== link2.from_task_id) {
                return false;
            }
            if (link1.to_task_id !== link2.to_task_id) {
                return false;
            }
            return true;
        };
        add = function(task, callback) {
            $api.call("task/add", task).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        $chat.task('add', res.data.task_id, res.data.mission_id);
                    }
                }
            });
        };
        edit = function(task, callback) {
            $api.call("task/edit", task).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        $chat.task('edit', res.data.task_id, res.data.mission_id);
                    }
                }
            });
        };
        remove = function(task, callback) {
            $api.call("task/remove", {
                task_id: task.task_id
            }).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                    if (res.data.err_code === 0) {
                        return $chat.task('remove', res.data.task_id, res.data.mission_id);
                    }
                }
            });
        };
        get_skills = function(task_id, callback) {
            return $api.call("task/get_skills", {
                task_id: task_id
            }).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        all_skills = function(callback) {
            $api.call("task/all_skills").then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        set_skills = function(task_id, skills, callback) {
            return $api.call("task/set_skills", {
                task_id: task_id,
                skills: skills
            }).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        get_comments = function(task_id, callback) {
            $api.call("task/get_comments", {
                task_id: task_id
            }).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        add_comment = function(task_id, comment, callback) {
            var params;
            params = {
                task_id: task_id,
                comment_type: 0,
                content: comment
            };
            $api.call("task/add_comment", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        remove_comment = function(task_comment_id, callback) {
            var params;
            params = {
                task_comment_id: task_comment_id
            };
            $api.call("task/remove_comment", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        get_candidates = function(task_id, callback) {
            var params;
            params = {
                task_id: task_id
            };
            $api.call("task/get_candidates", params).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        request_entrance = function(req, callback) {
            $api.call("task/request_entrance", req).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        help_entrance = function(req, callback) {
            $api.call("task/help_entrance", req).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        add_proclink = function(mission_id, from_task_id, to_task_id, callback) {
            $api.call("task/add_proclink", {
                mission_id: mission_id,
                from_task_id: from_task_id,
                to_task_id: to_task_id
            }).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        remove_proclink = function(from_task_id, to_task_id, callback) {
            $api.call("task/remove_proclink", {
                from_task_id: from_task_id,
                to_task_id: to_task_id
            }).then(function(res) {
                if (callback !== void 0) {
                    callback(res.data);
                }
            });
        };
        return {
            init: init,
            search: search,
            search_completed: search_completed,
            get_proclinks: get_proclinks,
            refresh_remaining: refresh_remaining,
            get_task: get_task,
            get_taskcount: get_taskcount,
            isEqualTasks: isEqualTasks,
            isEqual: isEqual,
            isEqualLinks: isEqualLinks,
            isEqualLink: isEqualLink,
            add: add,
            edit: edit,
            remove: remove,
            get_skills: get_skills,
            all_skills: all_skills,
            set_skills: set_skills,
            get_comments: get_comments,
            add_comment: add_comment,
            remove_comment: remove_comment,
            get_candidates: get_candidates,
            request_entrance: request_entrance,
            help_entrance: help_entrance,
            add_proclink: add_proclink,
            remove_proclink: remove_proclink
        };
    }
);