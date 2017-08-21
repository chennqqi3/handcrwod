'use strict'

angular.module('app.storage.task', [])

.factory('taskStorage', 
    ($rootScope, $api, $session, $dateutil, filterFilter, missionStorage, AUTH_EVENTS, $chat) ->
        # Search tasks
        search = (mission_id, priority_only) ->
            params = null
            if mission_id != undefined
                params = { mission_id: mission_id, search_mode: 1 } # search all
            else if priority_only != undefined
                params = { priority_only: 1 } # search all
                if $rootScope.cur_home != null
                    params.home_id = $rootScope.cur_home.home_id
            else if $rootScope.calendar_date != null
                params = { search_date: $rootScope.calendar_date, search_mode: 1 } # search all

            $api.call("task/search", params)
                .then((res) ->
                    if res.data.err_code == 0
                        tasks = res.data.tasks
                        tasks.forEach((task) ->
                            task.priority = task.priority == 1
                            task.complete_flag = task.complete_flag == 1
                            task.checked = false
                            )

                        if params == null
                            $rootScope.complete_offsets = {}
                        
                        if mission_id != undefined
                            $rootScope.tasks = tasks
                        else
                            $rootScope.tasks = tasks
                            refresh_remaining()
                            refresh_sort()
                    else
                        $rootScope.tasks = []

                    $rootScope.$broadcast('refreshed-tasks')

                    return $rootScope.tasks
                 )

        get_proclinks = (mission_id, callback) ->
            $api.call("task/get_proclinks", { mission_id : mission_id })
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )

            return

        # Get complated offset
        get_completed_offset = (mission_id) ->
            offset = $rootScope.complete_offsets[mission_id]
            offset = 0 if offset == undefined
            return offset

        # Set complated offset
        set_completed_offset = (mission_id, offset) ->
            $rootScope.complete_offsets[mission_id] = offset

        # Search completed tasks
        search_completed = (mission_id, priority_only) ->
            params = {}
            if $rootScope.calendar_date != null
                return

            if mission_id != undefined
                params = { mission_id: mission_id, search_mode: 2 }
                offset_key = mission_id
            else if priority_only != undefined
                params = { priority_only: 1 } # search all
                if $rootScope.cur_home != null
                    params.home_id = $rootScope.cur_home.home_id
                offset_key = 'home_' + params.home_id
                
            params.offset = get_completed_offset(offset_key)
            params.search_mode = 2
            params.limit = 10

            $api.call("task/search", params)
                .then((res) ->
                    if res.data.err_code == 0
                        tasks = res.data.tasks
                        tasks.forEach((task) ->
                            task.priority = task.priority == 1
                            task.complete_flag = task.complete_flag == 1
                            task.checked = false
                            )
                        
                        set_completed_offset(offset_key, params.offset + tasks.length)
                        push_tasks(tasks)

                        return tasks
                    else
                        return []
                )
        
        push_tasks = (tasks) ->
            for task in tasks
                found = false
                for t in $rootScope.tasks
                    if t.task_id == task.task_id
                        found = true
                        break
                $rootScope.tasks.push(task) if !found

            return

        # Refresh sort number
        refresh_sort = ->
            sort = 0
            $rootScope.tasks.forEach((task) ->
                if task.complete_flag == false and task.processed == 0
                    task.sort0 = sort
                    task.sort = sort
                    sort += 1
            )

        # Refresh remaining tasks
        refresh_remaining = ->
            $rootScope.priorityTasks = 0
            $rootScope.remainingITasks = 0
            $rootScope.remainingMTasks = 0
            remainingSel = 0
            $rootScope.tasks.forEach((task) ->
                if task.complete_flag == false and task.performer_id == $session.user_id
                    if task.priority == true
                        $rootScope.priorityTasks += 1
                    if task.mission_id == null
                        $rootScope.remainingITasks += 1
                    else
                        $rootScope.remainingMTasks += 1
                    if $rootScope.cur_mission != null and task.mission_id == $rootScope.cur_mission.mission_id
                        remainingSel += 1
            )
            if $rootScope.cur_mission != null
                $rootScope.remainingSelTasks = remainingSel
            else
                $rootScope.remainingSelTasks = 0
            missionStorage.refresh_remaining()

        # Get task from task_id
        get_task = (task_id) ->
            for task in $rootScope.tasks
                if task.task_id == task_id
                    return task
            return null

        # Get count of tasks in mission
        get_taskcount = (mission_id) ->
            count = 0
            for task in $rootScope.tasks
                if task.mission_id == mission_id
                    count += 1
            return count

        isEqualTasks = (tasks1, tasks2) ->
            return true if $api.is_empty(tasks1) && $api.is_empty(tasks2)
            if !$api.is_empty(tasks1) && !$api.is_empty(tasks2)
                return false if tasks1.length != tasks2.length

                for i in [0..tasks1.length - 1]
                    return false if !isEqual(tasks1[i], tasks2[i])

                return true
            else
                return false

        isEqual = (task1, task2) ->
            return true if $api.is_empty(task1) && $api.is_empty(task2)
            return false if task1.task_id != task2.task_id
            return false if task1.task_name != task2.task_name
            return false if task1.progress != task2.progress
            return false if task1.avartar != task2.avartar
            return false if task1.complete_flag != task2.complete_flag
            return false if task1.complete_time != task2.complete_time
            return false if task1.plan_end_date != task2.plan_end_date
            return false if task1.plan_hours != task2.plan_hours
            return false if task1.x != task2.x
            return false if task1.y != task2.y
            return true

        isEqualLinks = (links1, links2) ->
            return true if $api.is_empty(links1) && $api.is_empty(links2)
            if !$api.is_empty(links1) && !$api.is_empty(links2)
                return false if links1.length != links2.length

                for i in [0..links1.length - 1]
                    return false if !isEqualLink(links1[i], links2[i])

                return true
            else
                return false

        isEqualLink = (link1, link2) ->
            return true if $api.is_empty(link1) && $api.is_empty(link2)
            return false if link1.from_task_id != link2.from_task_id
            return false if link1.to_task_id != link2.to_task_id
            return true

        add = (task, callback) ->
            $api.call("task/add", task)
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.task('add', res.data.task_id, res.data.mission_id)
                    return
                )
            return

        edit = (task, callback) ->
            $api.call("task/edit", task)
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.task('edit', res.data.task_id, res.data.mission_id)
                    return
                )
            return

        remove = (task, callback) ->
            $api.call("task/remove", { task_id: task.task_id})
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                        if (res.data.err_code == 0)
                            $chat.task('remove', res.data.task_id, res.data.mission_id)
                )
            return

        get_skills = (task_id, callback) ->
            $api.call("task/get_skills", {task_id: task_id})
                .then((res) -> 
                    # res.data.skills
                    if callback != undefined
                        callback(res.data)
                    return
                )

        all_skills = (home_id, callback) ->
            $api.call("task/all_skills", {home_id: home_id})
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                    return
                 )

            return

        set_skills = (task_id, skills, callback) ->
            $api.call("task/set_skills", {task_id: task_id, skills: skills})
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )

        get_comments = (task_id, callback) ->
            $api.call("task/get_comments", {task_id: task_id})
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )
            return

        add_comment = (task_id, comment, callback) ->
            params = 
                task_id: task_id
                comment_type: 0
                content: comment

            $api.call("task/add_comment", params)
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )
            return

        remove_comment = (task_comment_id, callback) ->
            params = 
                task_comment_id: task_comment_id

            $api.call("task/remove_comment", params)
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )
            return

        get_candidates = (task_id, callback) ->
            params = 
                task_id: task_id

            $api.call("task/get_candidates", params)
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )
            return

        request_entrance = (req, callback) ->
            $api.call("task/request_entrance", req)
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )
            return

        help_entrance = (req, callback) ->
            $api.call("task/help_entrance", req)
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )
            return

        add_proclink = (mission_id, from_task_id, to_task_id, callback) ->
            $api.call("task/add_proclink", { mission_id: mission_id, from_task_id: from_task_id, to_task_id: to_task_id })
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )
            return

        remove_proclink = (from_task_id, to_task_id, callback) ->
            $api.call("task/remove_proclink", { from_task_id: from_task_id, to_task_id: to_task_id })
                .then((res) -> 
                    if callback != undefined
                        callback(res.data)
                    return
                )
            return

        return {
            search: search
            search_completed: search_completed
            
            get_proclinks: get_proclinks

            refresh_remaining: refresh_remaining
            get_task: get_task
            get_taskcount: get_taskcount

            isEqualTasks: isEqualTasks
            isEqual: isEqual

            isEqualLinks: isEqualLinks
            isEqualLink: isEqualLink

            add: add
            edit: edit
            remove: remove

            get_skills: get_skills
            all_skills: all_skills
            set_skills: set_skills

            get_comments: get_comments
            add_comment: add_comment
            remove_comment: remove_comment

            get_candidates: get_candidates
            request_entrance: request_entrance
            help_entrance: help_entrance

            add_proclink: add_proclink
            remove_proclink: remove_proclink

            set_completed_offset: set_completed_offset
        }
)