'use strict'

last_process = { x: 10, y: 10, z: 1 }

angular.module('app.task.process', [])

.directive('taskDraggable', 
    ($timeout, $rootScope, $parse, $api, taskStorage, HPRIV) ->
        return {
            restrict: 'A'
            require: 'ngModel'
            link: (scope, element, attrs, ngModel) ->
                initPanel = ->
                    if !($rootScope.canEditTask())
                        return

                    $(element).draggable(
                        drag: ( event, ui ) ->
                            $this = $(this)
            
                            difx = parseInt($this.css('left')) - $this.data('start-left')
                            dify = parseInt($this.css('top')) - $this.data('start-top')

                            $('.task-item.selected').each(->
                                if $(this).data('taskId') != $this.data('taskId')
                                    $(this).css('left', ($(this).data('start-left') + difx) + "px")
                                    $(this).css('top', ($(this).data('start-top') + dify) + "px")
                            )

                            scope.refreshLinks()

                        start: ->
                            maxz = 0
                            $('.task-item').each( -> 
                                z = parseInt($(this).css('z-index'))
                                maxz = z if z > maxz
                            )
                            if $(this).css('z-index') < maxz
                                $(this).css('z-index', maxz + 1);

                            $('.task-item.selected').each(->
                                $(this).data('start-left', parseInt($(this).css('left')))
                                $(this).data('start-top', parseInt($(this).css('top')))
                            )

                        stop: ->
                            $('.task-item.selected').each(->
                                task_id = $(this).data('taskId')

                                pos = $(this).position()
                                if pos.left < 0
                                    pos.left = 0
                                    $(this).css('left', 0)
                                if pos.top < 0
                                    pos.top = 0
                                    $(this).css('top', 0)

                                for task in scope.ptasks
                                    if task.task_id == task_id
                                        task.x = pos.left
                                        task.y = pos.top
                                        taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, x: task.x, y: task.y })
                            )
                            
                            scope.refreshLinks()

                    ).droppable(
                        accept: ".complete-handle"
                        activeClass: "ui-state-active"
                        hoverClass: "ui-state-hover"
                        drop: ( event, ui ) ->
                            scope.addLink(ui.draggable.parent().parent().data("taskId"), $(this).data("taskId"))
                    )

                    $(element).find('.complete-handle').draggable(
                        revert: true
                        helper: ->
                            return $("<div class='connector'><i class='fa fa-arrows-alt'></i></div>")
                        start: ->
                            maxz = 0
                            $('.task-item').each( -> 
                                z = parseInt($(this).css('z-index'))
                                maxz = z if z > maxz
                            )
                            if $(this).parent().parent().css('z-index') < maxz
                                $(this).parent().parent().css('z-index', maxz + 1);
                    )

                    scope.$on('refresh-progress', (event, t) ->
                        $this = $(element)
                        if t.task_id == $this.data('taskId')
                            pr = t.progress
                            pr = 0 if pr == null or pr == undefined or pr == 100
                            $(element).find('.progress').css('width', pr + '%')
                    )

                
                $timeout( -> 
                    initPanel()
                , 10)
        }
)

.controller('processCtrl', 
    ($scope, taskStorage, missionStorage, filterFilter, $rootScope, $routeParams, 
        HPRIV, logger, $session, $api, $timeout, $dateutil, $dialogs, $window) ->
        $scope.sync = ->
            $scope.mission_id = if $rootScope.cur_mission != null then $rootScope.cur_mission.mission_id else null

        $scope.sync()

        $scope.$on("synced-server", ->
            $scope.sync()

            if (!$scope.drag)
                $scope.refreshTasks(true)

            return
        )

        $scope.init = () ->
            $scope.showCritical = false
            $scope.critical = false
            $scope.root_tasks = []
            $scope.taskEditMode = false
            $scope.selectedTasks = []

            $scope.drag = false
            $scope.dragRect = null

            $scope.refreshTasks()

            return

        # Check privilege
        $scope.canTemplate = ->
            return $rootScope.canEditTask()

        $scope.canEdit = ->
            return $rootScope.canEditTask() && $scope.selectedTasks.length == 1

        $scope.refreshLinks = (fromView) ->
            context = getContext()
            return false if context == null
            board = $('#board')
            page = $('.page-process')
            wh = $scope.maxWH()
            w = wh.width
            h = wh.height

            ow = board.width()
            oh = board.height()

            context.clearRect(0, 0, ow, oh)
        
            if ow < w
                w = w + 100
            else if ow < w + 100
                w = ow + 100
            else
                w = ow
            if context.canvas.width != w
                context.canvas.width = w
                board.width(w)

            if oh < h
                h = h + 100
            else if oh < h + 100
                h = oh + 100
            else
                h = oh
            if context.canvas.height != h
                context.canvas.height = h
                board.height(h)

            $scope.links.forEach( (link) ->
                drawConnect(link, $scope.taskItemFromId(link.from_task_id), $scope.taskItemFromId(link.to_task_id), link.critical)
            )

            if $scope.dragRect != null
                context.beginPath()
                context.rect($scope.dragRect.x, $scope.dragRect.y, $scope.dragRect.w, $scope.dragRect.h)
                context.lineWidth = 1
                context.strokeStyle = '#7C4DFF'
                context.stroke()

            return true

        $scope.maxWH = ->
            board = $('#board')
            mw = board.width() - 100
            mh = board.height() - 100
            $('.task-item').each( ->
                pos = $(this).position()
                if pos.left + $(this).outerWidth() > mw
                    mw = pos.left + $(this).outerWidth()
                if pos.top + $(this).outerHeight() > mh
                    mh = pos.top + $(this).outerHeight()
            ) 
            return { width: mw, height: mh }

        $scope.refreshBackImage = ->
            cover = ''
            if $rootScope.taskMode == 2 && $rootScope.cur_mission && $rootScope.cur_mission.prc_back_url != null
                if $rootScope.cur_mission.prc_back_pos == 1
                    back_pos = " repeat"
                else if $rootScope.cur_mission.prc_back_pos == 2
                    back_pos = " no-repeat center center"
                else if $rootScope.cur_mission.prc_back_pos == 3
                    back_pos = " no-repeat left top"
                else
                    back_pos = " no-repeat center center"
                    cover = 'cover'
                $('#board').removeClass('default-back')
                $('#board').css('background', 'url(' + encodeURI($rootScope.cur_mission.prc_back_url) + ') ' + back_pos)
            else
                $('#board').addClass('default-back')
                $('#board').css('background', '')

            $('#board').css('-webkit-background-size', cover)
            $('#board').css('-moz-background-size', cover)
            $('#board').css('-o-background-size', cover)
            $('#board').css('background-size', cover)
            return

        $scope.refreshBackImage()

        $scope.$on('refresh_back_image', ->
            $scope.refreshBackImage()
        )

        $scope.refreshTasks = (sync) ->
            last_process = { x: 10, y: 10, z: 1 }
            $scope.showCritical = false
            $scope.removableProclink = false

            if sync != true
                $scope.ptasks = []
                $scope.links = []

            if $scope.mission_id != null && $rootScope.cur_mission != null
                tasks = $rootScope.tasks
                tasks.forEach((task) -> task.inited = null)
                if !taskStorage.isEqualTasks($scope.ptasks, tasks)
                    $scope.ptasks = tasks

                taskStorage.get_proclinks($scope.mission_id, (res) ->
                    if res.err_code == 0
                        if !taskStorage.isEqualLinks($scope.links, res.links)
                            $scope.links = res.links

                            $timeout(->
                                $scope.checkCritical()
                                $scope.refreshLinks()

                                $scope.refreshBackImage()
                            , 200)

                    else
                        logger.logError(res.err_msg)
                )

            return

        $scope.searchFilter = (task) ->
            #if task.complete_flag == true and task.processed == 0
            #    return false

            return true

        $scope.$on('reload_session', ->
            $scope.init()
        )

        $scope.$on('select-mission', (event) ->
            #$scope.init()
        )

        $scope.$on('refreshed-tasks', ->
            $scope.init()
        )

        $scope.checkLoop = (from, to) ->
            links = $scope.links
            for link in links
                if link.from_task_id == from
                    if link.to_task_id == to
                        return true
                    else
                        ret = $scope.checkLoop(link.to_task_id, to)
                        return true if ret
            return false

        $scope.resetProcLevel = () ->
            links = $scope.links
            $scope.ptasks.forEach((task) ->
                oldlevel = task.proclevel
                if task.processed == 0
                    task.proclevel = 0 
                else
                    task.proclevel = $scope.getProcLevel(task.task_id)

                if task.proclevel != oldlevel
                    taskStorage.edit({ task_id: task.task_id, mission_id: task.mission_id, proclevel: task.proclevel })
            )

        $scope.checkCritical = () ->
            # check critical
            if $scope.ptasks.length <= 1
                $scope.showCritical = false
                return

            $scope.checkLinked(null)

            $scope.findCritical(null)

            if $scope.critical_path != null
                $scope.showCritical = true
            else
                $scope.showCritical = false

            return

        $scope.$on('refresh-critical', ->
            $scope.checkCritical()
            $scope.refreshLinks()
        )

        $scope.findCritical = (path) ->
            if path == null
                $scope.max_hours = 0
                #$scope.max_tasks = 0
                $scope.critical_path = null
                for root in $scope.root_tasks
                    $scope.findCritical([root])

                if $scope.critical_path != null
                    for link in $scope.links
                        link.critical = false

                    for i in [0..($scope.critical_path.length - 2)]
                        from_task_id = $scope.critical_path[i].task_id
                        to_task_id = $scope.critical_path[i + 1].task_id

                        for link in $scope.links
                            if link.from_task_id == from_task_id and link.to_task_id == to_task_id
                                link.critical = true
                return

            task = path[path.length - 1]
            exist_to_task = false
            for link in $scope.links
                atask = null
                # find to task
                if link.from_task_id == task.task_id
                    atask = $scope.getTask(link.to_task_id)

                if atask!= null and atask.task_id != task.task_id
                    $scope.findCritical(path.concat([atask]))
                    exist_to_task = true

            if exist_to_task == false
                hours = 0
                for t in path
                    hours += (t.plan_hours * 1)

                if hours >= $scope.max_hours #and path.length >= $scope.max_tasks
                    $scope.max_hours = hours
                    #$scope.max_tasks = path.length
                    $scope.critical_path = path

        $scope.checkLinked = (task) ->
            if task == null
                $scope.critical = true
                $scope.root_tasks = []
                
                for task in $scope.ptasks
                    # 連結されない完了済みタスクは無視
                    task.linked = if task.complete_flag == true and task.processed == 0 then true else false
                
                if $scope.ptasks.length > 0
                    $scope.checkLinked($scope.ptasks[0])

                for task in $scope.ptasks
                    if task.linked == false
                        $scope.critical = false
                        break

                if $scope.critical == false
                    $scope.root_tasks = []
                else if $scope.root_tasks.length == 0
                    $scope.critical = false
                return

            task.linked = true
            to_task_id = null
            from_task_id = null

            for link in $scope.links
                atask = null
                # find to task
                if link.from_task_id == task.task_id
                    to_task_id = link.to_task_id
                    atask = $scope.getTask(to_task_id)
                # find from task
                if link.to_task_id == task.task_id
                    from_task_id = link.from_task_id
                    atask = $scope.getTask(from_task_id)

                if atask!= null and atask.task_id != task.task_id and atask.linked == false
                    $scope.checkLinked(atask)

            if from_task_id == null and to_task_id != null
                $scope.root_tasks.push(task)

        $scope.getTask = (task_id) ->
            for task in $scope.ptasks
                if task.task_id == task_id
                    return task
            return null

        $scope.getProcLevel = (task_id) ->
            links = $scope.links
            for link in links
                if link.to_task_id == task_id
                    return $scope.getProcLevel(link.from_task_id) + 1
            return 0
        
        $scope.initItem = (task) ->
            task_id = task.task_id
            if task.inited == null
                element = $('.task-item[data-task-id="' + task_id + '"]')
                if task.x == null
                    task.x = last_process.x
                    task.y = last_process.y
                    last_process.x += 3
                    last_process.y += 15
                last_process.z += 1
                if task.x < 0
                    task.x = 0
                if task.y < 0 
                    task.y = 0
                $(element).css('left', task.x)
                $(element).css('top', task.y)
                $(element).css('z-index', last_process.z)
                $(element).data('taskId', task_id)
                task.inited = true
            else
                $(element).css('z-index', task.proclevel)
            return true

        $scope.addLink = (from_task_id, to_task_id) ->
            if from_task_id != to_task_id
                found = false
                $scope.links.forEach((link) ->
                    if link.from_task_id == to_task_id and link.to_task_id == from_task_id or link.from_task_id == from_task_id and link.to_task_id == to_task_id
                        found = true
                )
                return if found

                if $scope.checkLoop(to_task_id, from_task_id)
                    logger.logError("循環リンクは作成できません。")
                    return

                $scope.links.push({ from_task_id: from_task_id, to_task_id: to_task_id })
                $scope.checkCritical()
                $scope.refreshLinks()
                $scope.setProcessed(from_task_id, 1)
                $scope.setProcessed(to_task_id, 1)
                $scope.refreshProcessed()
                $scope.resetProcLevel()

                taskStorage.add_proclink($scope.mission_id, from_task_id, to_task_id, (res) ->
                    if res.err_code != 0
                        logger.logError(res.err_msg)
                )
                return

        $scope.setProcessed = (task_id, processed) ->
            $scope.ptasks.forEach((task) ->
                if task.task_id == task_id
                    task.processed = processed
            )

        $scope.taskItemFromId = (task_id) ->
            taskItem = null
            $('.task-item').each( ->
                taskItem = $(this) if $(this).data('taskId') == task_id
            )

            return taskItem
        
        $scope.mousedownCanvas = (evt) ->
            x = evt.pageX - $('#lines').offset().left #x = evt.offsetX
            y = evt.pageY - $('#lines').offset().top #y = evt.offsetY
            
            changed = false
            $scope.links.forEach((link) ->
                selected = isOnLine(link.x1, link.y1, link.x2, link.y2, x, y)
                if link.selected and selected
                    selected = false

                if link.selected != selected
                    changed = true
                    link.selected = selected

                if link.selected
                    $scope.removableProclink = true
            )
            
            if changed
                $scope.refreshLinks()

            if !$window.mobilecheck()
                $scope.drag = true
                $scope.dragRect =
                    x: x
                    y: y
                    w: 0
                    h: 0

                $scope.selectedTasks = []
                $scope.refreshSelectTask()

            return

        $('body').mousemove((evt) ->
            if $scope.drag
                x = $scope.dragRect.x
                y = $scope.dragRect.y
                w = evt.pageX - $('#lines').offset().left - $scope.dragRect.x
                h = evt.pageY - $('#lines').offset().top - $scope.dragRect.y
                #console.log("x=" + w + " y=" + h)

                if $scope.dragRect.w != w or $scope.dragRect.h != h
                    $scope.dragRect.w = w
                    $scope.dragRect.h = h
                    $scope.refreshLinks()

                    if w < 0
                        x = x + w
                        w = -w
                    if h < 0
                        y = y + h
                        h = -h

                    $scope.selectedTasks = []
                    for task in $scope.ptasks
                        t = $scope.taskItemFromId(task.task_id)
                        if t != null
                            pos = t.position()
                            t_x = pos.left
                            t_y = pos.top
                            t_w = t.outerWidth(true)
                            t_h = t.outerHeight(true)

                            if not (x + w < t_x or t_x + t_w < x or y + h < t_y or t_y + t_h < y)
                                $scope.selectedTasks.push(task)

                    $scope.refreshSelectTask()

        )

        $('body').mouseup((evt) ->
            if $scope.drag
                $scope.drag = false
                $scope.dragRect = null
                $scope.refreshLinks()
        )

        $scope.removeProclink = ->
            i = 0
            $scope.links.forEach((link) ->
                link.i = i
                if link.selected
                    taskStorage.remove_proclink(link.from_task_id, link.to_task_id, (res) ->
                        if res.err_code != 0
                            logger.logError(res.err_msg)
                        else
                            $scope.links.splice(link.i, 1)
                            $scope.checkCritical()
                            $scope.refreshLinks()
                            $scope.setProcessed(link.from_task_id, res.from_processed)
                            $scope.setProcessed(link.to_task_id, res.to_processed)
                            $scope.refreshProcessed()
                            $scope.resetProcLevel()
                        $scope.removableProclink = false
                    )
                i += 1
            )

        $scope.refreshProcessed = ->
            $scope.ptasks.forEach((task) ->
                task_id = task.task_id
                task_item = $('.task-item[data-task-id="' + task_id + '"]')
                if task.processed == 1
                    task_item.addClass('processed')
                else
                    task_item.removeClass('processed')
            )

        $scope.is_past = (task) ->
            return $dateutil.is_past(task.plan_end_date) && task.complete_flag != true

        $scope.refreshSelectTask = () ->
            $('.task-item').removeClass('selected')
            for task in $scope.selectedTasks
                t = $scope.taskItemFromId(task.task_id)
                t.addClass('selected')

            if $scope.selectedTasks.length == 0
                if $scope.taskEditMode
                    $rootScope.$broadcast('select-task', null)

        $scope.selectTask = (task) ->
            if $scope.selectedTasks.length > 1
                for t in $scope.selectedTasks
                    if t == task
                        return

            if $scope.selectedTasks.length == 1 and $scope.selectedTasks[0] == task
                #$scope.selectedTasks = []
                #$rootScope.$broadcast('select-task', null)
                return
            else
                $scope.selectedTasks = [task]
                if $scope.taskEditMode
                    $rootScope.$broadcast('select-task', task)

            $scope.refreshSelectTask()

        $scope.editTask = () ->
            if $scope.selectedTasks.length == 1
                $rootScope.$broadcast('select-task', $scope.selectedTasks[0])

        $scope.$on('select-task', (event, task) ->
            $scope.taskEditMode = task != null
        )

        $scope.addTask = () ->
            $dialogs.addTask($rootScope.cur_mission)

        # Title
        $scope.title = () ->
            title = ""
            # mission mode
            if $rootScope.cur_mission
                title = $rootScope.cur_mission.mission_name
            else
                title = "チャットルームを選択してください"

            return title

        # start init
        $scope.init()

)

getContext = ->
    canvas = document.getElementById('lines')
    return canvas.getContext('2d') if canvas != null
    return null

drawLine = (x1, y1, x2, y2, color) ->
    context = getContext()
    return if context == null
    context.beginPath()
    context.moveTo(x1, y1)
    context.lineTo(x2, y2)
    context.lineWidth = 3
    context.strokeStyle = color
    context.stroke()

drawConnect = (link, fromTask, toTask, critical) ->
    return if fromTask == null or toTask == null
    fromPos = fromTask.position()
    toPos = toTask.position()
    link.x1 = x1 = fromPos.left + fromTask.outerWidth(true) / 2
    link.y1 = y1 = fromPos.top + fromTask.outerHeight(true) / 2
    link.x2 = x2 = toPos.left + toTask.outerWidth(true) / 2
    link.y2 = y2 = toPos.top + toTask.outerHeight(true) / 2

    if link.selected 
        color = '#7C4DFF'
    else if critical
        color = '#FF4081'
    else
        color = '#3F51B5'

    interFrom = checkLineRectIntersection(x1, y1, x2, y2, fromPos.left - 2, fromPos.top - 2, fromTask.outerWidth(true) + 4, fromTask.outerHeight(true) + 4)
    interTo = checkLineRectIntersection(x1, y1, x2, y2, toPos.left - 2, toPos.top - 2, toTask.outerWidth(true) + 4, toTask.outerHeight(true) + 4)
    if interFrom != null && interTo != null
        drawLine(interFrom.x, interFrom.y, interTo.x, interTo.y, color)

    interTo = checkLineRectIntersection(x1, y1, x2, y2, toPos.left - 3, toPos.top - 3, toTask.outerWidth(true) + 6, toTask.outerHeight(true) + 6)
    if interTo != null
        # draw arrow
        p1 = getArrowPos(interTo.x, interTo.y, x1, y1, -20, 10)
        p2 = getArrowPos(interTo.x, interTo.y, x1, y1, 20, 10)
        drawLine(p1.x, p1.y, interTo.x, interTo.y, color)
        drawLine(p2.x, p2.y, interTo.x, interTo.y, color)

checkLineRectIntersection = (l1_sx, l1_sy, l1_ex, l1_ey, r_x, r_y, r_w, r_h) ->
    res = checkLineIntersection(l1_sx, l1_sy, l1_ex, l1_ey, r_x, r_y, r_x + r_w - 1, r_y)
    return res if res.onLine1 and res.onLine2
        
    res = checkLineIntersection(l1_sx, l1_sy, l1_ex, l1_ey, r_x + r_w - 1, r_y, r_x + r_w - 1, r_y + r_h - 1)
    return res if res.onLine1 and res.onLine2

    res = checkLineIntersection(l1_sx, l1_sy, l1_ex, l1_ey, r_x + r_w - 1, r_y + r_h - 1, r_x, r_y + r_h - 1)
    return res if res.onLine1 and res.onLine2

    res = checkLineIntersection(l1_sx, l1_sy, l1_ex, l1_ey, r_x, r_y + r_h - 1, r_x, r_y)
    return res if res.onLine1 and res.onLine2

    return null

checkLineIntersection = (l1_sx, l1_sy, l1_ex, l1_ey, l2_sx, l2_sy, l2_ex, l2_ey) ->
    # if the lines intersect, the result contains the x and y of the intersection (treating the lines as infinite) and booleans for whether line segment 1 or line segment 2 contain the point
    result = 
        x: null
        y: null
        onLine1: false
        onLine2: false

    denominator = ((l2_ey - l2_sy) * (l1_ex - l1_sx)) - ((l2_ex - l2_sx) * (l1_ey - l1_sy))

    if denominator == 0
        return result
    
    a = l1_sy - l2_sy
    b = l1_sx - l2_sx

    numerator1 = ((l2_ex - l2_sx) * a) - ((l2_ey - l2_sy) * b)
    numerator2 = ((l1_ex - l1_sx) * a) - ((l1_ey - l1_sy) * b)
    a = numerator1 / denominator
    b = numerator2 / denominator

    # if we cast these lines infinitely in both directions, they intersect here:
    result.x = l1_sx + (a * (l1_ex - l1_sx))
    result.y = l1_sy + (a * (l1_ey - l1_sy))

    # if line1 is a segment and line2 is infinite, they intersect if:
    if a > 0 and a < 1
        result.onLine1 = true
    
    # if line2 is a segment and line1 is infinite, they intersect if:
    if b > 0 and b < 1
        result.onLine2 = true
    
    # if line1 and line2 are segments, they intersect if both of the above are true
    return result

getArrowPos = (x1, y1, x2, y2, ang, sz) ->
    result = 
        x: null
        y: null
    
    ang = Math.PI / 180 * ang
    l = Math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2)
    xd = (x2 - x1) / l
    yd = (y2 - y1) / l
    result.x = x1 + (Math.cos(ang) * xd - Math.sin(ang) * yd) * sz
    result.y = y1 + (Math.sin(ang) * xd + Math.cos(ang) * yd) * sz
    return result


isOnLine = (x1, y1, x2, y2, px, py) ->
    f1 = (somex)->
        return (y2 - y1) / (x2 - x1) * (somex - x1) + y1
    f2 = (somey)->
        return (x2 - x1) / (y2 - y1) * (somey - y1) + x1

    return (Math.abs(f1(px) - py) < 10 and (px >= x1 and px <= x2 or px >= x2 and px <= x1)) or Math.abs(f2(py) - px) < 10 and (py >= y1 and py <= y2 or py >= y2 and py <= y1)