
last_process = { x: 10, y: 10, z: 1 };

angular.module('app.process', [])

.directive('progressBack', 
    function($timeout, $rootScope, $parse, $api) {
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function(scope, element, attrs, ngModel) {
                initPanel = function() {
                    progress = ngModel.$modelValue;
                    console.log("progress" + progress);
                    if (progress == null || progress == undefined)
                        progress = 0;
                    $(element).css("width", progress + "%");
                }
                
                $timeout( function() {
                    initPanel();
                }, 10);
            }
        }
    }
)

.controller('processCtrl', 
    function($scope, $rootScope, $api, $stateParams, taskStorage, $dateutil, $timeout) {
        $scope.$on('$ionicView.loaded', function() {
            $('#process_view').css('height', window.screen.height - 44); // header height: 44px
        });

        $scope.init = function() {
            $scope.mission_id = parseInt($stateParams.mission_id, 10);

            taskStorage.search($scope.mission_id).then(function(tasks) {
                $scope.ptasks = tasks;

                taskStorage.get_proclinks($scope.mission_id, function(res) {
                    if (res.err_code == 0) {
                        $scope.links = res.links;

                        $timeout(function() {
                            $scope.checkCritical();
                            $scope.refreshLinks();
                        }, 200);
                    }
                    else
                        logger.logError(res.err_msg);
                });
            });
        }
        $scope.refreshLinks = function(fromView) {
            var board, context, h, oh, ow, w, wh;
            context = getContext();
            if (context === null) {
                return false;
            }
            board = $('#board');
            wh = $scope.maxWH();
            w = wh.width;
            h = wh.height;
            ow = board.width();
            oh = board.height();
            context.clearRect(0, 0, ow, oh);
            if (ow < w) {
                w = w + 100;
            } else if (ow < w + 100) {
                w = ow + 100;
            } else {
                w = ow;
            }
            if (context.canvas.width !== w) {
                context.canvas.width = w;
                board.width(w);
            }
            if (oh < h) {
                h = h + 100;
            } else if (oh < h + 100) {
                h = oh + 100;
            } else {
                h = oh;
            }
            if (context.canvas.height !== h) {
                context.canvas.height = h;
                board.height(h);
            }
            $scope.links.forEach(function(link) {
                return drawConnect(link, $scope.taskItemFromId(link.from_task_id), $scope.taskItemFromId(link.to_task_id), link.critical);
            });
            return true;
        };
        $scope.maxWH = function() {
            var board, mh, mw;
            board = $('#board');
            mw = board.width() - 100;
            mh = board.height() - 100;
            $('.task-item').each(function() {
                var pos;
                pos = $(this).position();
                if (pos.left + $(this).outerWidth() > mw) {
                    mw = pos.left + $(this).outerWidth();
                }
                if (pos.top + $(this).outerHeight() > mh) {
                    return mh = pos.top + $(this).outerHeight();
                }
            });
            return {
                width: mw,
                height: mh
            };
        };
        $scope.refreshBackImage = function() {
            var back_pos, cover;
            cover = '';
            if ($rootScope.cur_mission && $rootScope.cur_mission.prc_back_url !== null) {
                if ($rootScope.cur_mission.prc_back_pos === 1) {
                    back_pos = " repeat";
                } else if ($rootScope.cur_mission.prc_back_pos === 2) {
                    back_pos = " no-repeat center center";
                } else if ($rootScope.cur_mission.prc_back_pos === 3) {
                    back_pos = " no-repeat left top";
                } else {
                    back_pos = " no-repeat center center";
                    cover = 'cover';
                }
                $('#board').removeClass('default-back');
                $('#board').css('background', 'url(' + encodeURI($rootScope.cur_mission.prc_back_url) + ') ' + back_pos);
            } else {
                $('#board').addClass('default-back');
                $('#board').css('background', '');
            }
            $('#board').css('-webkit-background-size', cover);
            $('#board').css('-moz-background-size', cover);
            $('#board').css('-o-background-size', cover);
            $('#board').css('background-size', cover);
        };
        $scope.refreshTasks = function(sync) {
            last_process = {
                x: 10,
                y: 10,
                z: 1
            };
            $scope.showCritical = false;
            $scope.removableProclink = false;
            if (sync !== true) {
                $scope.ptasks = [];
                $scope.links = [];
            }
            if ($scope.mission_id !== null && $rootScope.cur_mission !== null) {
                taskStorage.search($scope.mission_id).then(function(tasks) {
                    tasks.forEach(function(task) {
                        return task.inited = null;
                    });
                    if (!taskStorage.isEqualTasks($scope.ptasks, tasks)) {
                        $scope.ptasks = tasks;
                    }
                    return taskStorage.get_proclinks($scope.mission_id, function(res) {
                        if (res.err_code === 0) {
                            if (!taskStorage.isEqualLinks($scope.links, res.links)) {
                                $scope.links = res.links;
                                return $timeout(function() {
                                    $scope.checkCritical();
                                    $scope.refreshLinks();
                                    $scope.refreshBackImage();
                                }, 200);
                            }
                        } else {
                            return logger.logError(res.err_msg);
                        }
                    });
                });
            }
        };
        $scope.searchFilter = function(task) {
            if (task.complete_flag === true && task.processed === 0) {
                return false;
            }
            return true;
        };
        $scope.checkLoop = function(from, to) {
            var j, len, link, links, ret;
            links = $scope.links;
            for (j = 0, len = links.length; j < len; j++) {
                link = links[j];
                if (link.from_task_id === from) {
                    if (link.to_task_id === to) {
                        return true;
                    } else {
                        ret = $scope.checkLoop(link.to_task_id, to);
                        if (ret) {
                            return true;
                        }
                    }
                }
            }
            return false;
        };
        $scope.resetProcLevel = function() {
            var links;
            links = $scope.links;
            return $scope.ptasks.forEach(function(task) {
                var oldlevel;
                oldlevel = task.proclevel;
                if (task.processed === 0) {
                    task.proclevel = 0;
                } else {
                    task.proclevel = $scope.getProcLevel(task.task_id);
                }
                if (task.proclevel !== oldlevel) {
                    return $api.call("task/edit", {
                        task_id: task.task_id,
                        proclevel: task.proclevel
                    });
                }
            });
        };
        $scope.checkCritical = function() {
            if ($scope.ptasks.length <= 1) {
                $scope.showCritical = false;
                return;
            }
            $scope.checkLinked(null);
            $scope.findCritical(null);
            if ($scope.critical_path !== null) {
                $scope.showCritical = true;
            } else {
                $scope.showCritical = false;
            }
        };
        $scope.$on('refresh-critical', function() {
            $scope.checkCritical();
            return $scope.refreshLinks();
        });
        $scope.findCritical = function(path) {
            var atask, exist_to_task, from_task_id, hours, i, j, k, len, len1, len2, len3, len4, link, m, n, o, p, ref, ref1, ref2, ref3, ref4, root, t, task, to_task_id;
            if (path === null) {
                $scope.max_hours = 0;
                $scope.max_tasks = 0;
                $scope.critical_path = null;
                ref = $scope.root_tasks;
                for (j = 0, len = ref.length; j < len; j++) {
                    root = ref[j];
                    $scope.findCritical([root]);
                }
                if ($scope.critical_path !== null) {
                    ref1 = $scope.links;
                    for (k = 0, len1 = ref1.length; k < len1; k++) {
                        link = ref1[k];
                        link.critical = false;
                    }
                    for (i = m = 0, ref2 = $scope.critical_path.length - 2; 0 <= ref2 ? m <= ref2 : m >= ref2; i = 0 <= ref2 ? ++m : --m) {
                        from_task_id = $scope.critical_path[i].task_id;
                        to_task_id = $scope.critical_path[i + 1].task_id;
                        ref3 = $scope.links;
                        for (n = 0, len2 = ref3.length; n < len2; n++) {
                            link = ref3[n];
                            if (link.from_task_id === from_task_id && link.to_task_id === to_task_id) {
                                link.critical = true;
                            }
                        }
                    }
                }
                return;
            }
            task = path[path.length - 1];
            exist_to_task = false;
            ref4 = $scope.links;
            for (o = 0, len3 = ref4.length; o < len3; o++) {
                link = ref4[o];
                atask = null;
                if (link.from_task_id === task.task_id) {
                    atask = $scope.getTask(link.to_task_id);
                }
                if (atask !== null && atask.task_id !== task.task_id) {
                    $scope.findCritical(path.concat([atask]));
                    exist_to_task = true;
                }
            }
            if (exist_to_task === false) {
                hours = 0;
                for (p = 0, len4 = path.length; p < len4; p++) {
                    t = path[p];
                    hours += t.plan_hours * 1;
                }
                if (hours >= $scope.max_hours && path.length >= $scope.max_tasks) {
                    $scope.max_hours = hours;
                    $scope.max_tasks = path.length;
                    return $scope.critical_path = path;
                }
            }
        };
        $scope.checkLinked = function(task) {
            var atask, from_task_id, j, k, len, len1, len2, link, m, ref, ref1, ref2, to_task_id;
            if (task === null) {
                $scope.critical = true;
                $scope.root_tasks = [];
                ref = $scope.ptasks;
                for (j = 0, len = ref.length; j < len; j++) {
                    task = ref[j];
                    task.linked = task.complete_flag === true && task.processed === 0 ? true : false;
                }
                if ($scope.ptasks.length > 0) {
                    $scope.checkLinked($scope.ptasks[0]);
                }
                ref1 = $scope.ptasks;
                for (k = 0, len1 = ref1.length; k < len1; k++) {
                    task = ref1[k];
                    if (task.linked === false) {
                        $scope.critical = false;
                        break;
                    }
                }
                if ($scope.critical === false) {
                    $scope.root_tasks = [];
                } else if ($scope.root_tasks.length === 0) {
                    $scope.critical = false;
                }
                return;
            }
            task.linked = true;
            to_task_id = null;
            from_task_id = null;
            ref2 = $scope.links;
            for (m = 0, len2 = ref2.length; m < len2; m++) {
                link = ref2[m];
                atask = null;
                if (link.from_task_id === task.task_id) {
                    to_task_id = link.to_task_id;
                    atask = $scope.getTask(to_task_id);
                }
                if (link.to_task_id === task.task_id) {
                    from_task_id = link.from_task_id;
                    atask = $scope.getTask(from_task_id);
                }
                if (atask !== null && atask.task_id !== task.task_id && atask.linked === false) {
                    $scope.checkLinked(atask);
                }
            }
            if (from_task_id === null && to_task_id !== null) {
                return $scope.root_tasks.push(task);
            }
        };
        $scope.getTask = function(task_id) {
            var j, len, ref, task;
            ref = $scope.ptasks;
            for (j = 0, len = ref.length; j < len; j++) {
                task = ref[j];
                if (task.task_id === task_id) {
                    return task;
                }
            }
            return null;
        };
        $scope.getProcLevel = function(task_id) {
            var j, len, link, links;
            links = $scope.links;
            for (j = 0, len = links.length; j < len; j++) {
                link = links[j];
                if (link.to_task_id === task_id) {
                    return $scope.getProcLevel(link.from_task_id) + 1;
                }
            }
            return 0;
        };
        $scope.initItem = function(task) {
            var element, task_id;
            task_id = task.task_id;
            if (task.inited == null) {
                element = $('.task-item[data-task-id="' + task_id + '"]');
                if (task.x === null) {
                    task.x = last_process.x;
                    task.y = last_process.y;
                    last_process.x += 3;
                    last_process.y += 15;
                }
                last_process.z += 1;
                if (task.x < 0) {
                    task.x = 0;
                }
                if (task.y < 0) {
                    task.y = 0;
                }
                $(element).css('left', task.x);
                $(element).css('top', task.y);
                $(element).css('z-index', last_process.z);
                $(element).data('taskId', task_id);
                task.inited = true;
            } else {
                $(element).css('z-index', task.proclevel);
            }
            return true;
        };
        $scope.addLink = function(from_task_id, to_task_id) {
            var found;
            if (from_task_id !== to_task_id) {
                found = false;
                $scope.links.forEach(function(link) {
                    if (link.from_task_id === to_task_id && link.to_task_id === from_task_id || link.from_task_id === from_task_id && link.to_task_id === to_task_id) {
                        return found = true;
                    }
                });
                if (found) {
                    return;
                }
                if ($scope.checkLoop(to_task_id, from_task_id)) {
                    logger.logError("循環リンクは作成できません。");
                    return;
                }
                $scope.links.push({
                    from_task_id: from_task_id,
                    to_task_id: to_task_id
                });
                $scope.checkCritical();
                $scope.refreshLinks();
                $scope.setProcessed(from_task_id, 1);
                $scope.setProcessed(to_task_id, 1);
                $scope.refreshProcessed();
                $scope.resetProcLevel();
                return $api.call("task/add_proclink", {
                    mission_id: $scope.mission_id,
                    from_task_id: from_task_id,
                    to_task_id: to_task_id
                }).then(function(res) {
                    if (res.data.err_code !== 0) {
                        return logger.logError(res.data.err_msg);
                    }
                });
            }
        };
        $scope.setProcessed = function(task_id, processed) {
            return $scope.ptasks.forEach(function(task) {
                if (task.task_id === task_id) {
                    return task.processed = processed;
                }
            });
        };
        $scope.taskItemFromId = function(task_id) {
            var taskItem;
            taskItem = null;
            $('.task-item').each(function() {
                if ($(this).data('taskId') === task_id) {
                    return taskItem = $(this);
                }
            });
            return taskItem;
        };
        $scope.refreshProcessed = function() {
            return $scope.ptasks.forEach(function(task) {
                var task_id, task_item;
                task_id = task.task_id;
                task_item = $('.task-item[data-task-id="' + task_id + '"]');
                if (task.processed === 1) {
                    return task_item.addClass('processed');
                } else {
                    return task_item.removeClass('processed');
                }
            });
        };
        $scope.is_past = function(task) {
            return $dateutil.is_past(task.plan_end_date) && task.complete_flag !== true;
        };

        $scope.init();
    }
);


getContext = function() {
    var canvas;
    canvas = document.getElementById('lines');
    if (canvas !== null) {
        return canvas.getContext('2d');
    }
    return null;
};

drawLine = function(x1, y1, x2, y2, color) {
    var context;
    context = getContext();
    if (context === null) {
        return;
    }
    context.beginPath();
    context.moveTo(x1, y1);
    context.lineTo(x2, y2);
    context.lineWidth = 3;
    context.strokeStyle = color;
    return context.stroke();
};

drawConnect = function(link, fromTask, toTask, critical) {
    var color, fromPos, interFrom, interTo, p1, p2, toPos, x1, x2, y1, y2;
    if (fromTask === null || toTask === null) {
        return;
    }
    fromPos = fromTask.position();
    toPos = toTask.position();
    link.x1 = x1 = fromPos.left + fromTask.outerWidth(true) / 2;
    link.y1 = y1 = fromPos.top + fromTask.outerHeight(true) / 2;
    link.x2 = x2 = toPos.left + toTask.outerWidth(true) / 2;
    link.y2 = y2 = toPos.top + toTask.outerHeight(true) / 2;
    if (link.selected) {
        color = '#7C4DFF';
    } else if (critical) {
        color = '#FF4081';
    } else {
        color = '#3F51B5';
    }
    interFrom = checkLineRectIntersection(x1, y1, x2, y2, fromPos.left - 2, fromPos.top - 2, fromTask.outerWidth(true) + 4, fromTask.outerHeight(true) + 4);
    interTo = checkLineRectIntersection(x1, y1, x2, y2, toPos.left - 2, toPos.top - 2, toTask.outerWidth(true) + 4, toTask.outerHeight(true) + 4);
    if (interFrom !== null && interTo !== null) {
        drawLine(interFrom.x, interFrom.y, interTo.x, interTo.y, color);
    }
    interTo = checkLineRectIntersection(x1, y1, x2, y2, toPos.left - 3, toPos.top - 3, toTask.outerWidth(true) + 6, toTask.outerHeight(true) + 6);
    if (interTo !== null) {
        p1 = getArrowPos(interTo.x, interTo.y, x1, y1, -20, 10);
        p2 = getArrowPos(interTo.x, interTo.y, x1, y1, 20, 10);
        drawLine(p1.x, p1.y, interTo.x, interTo.y, color);
        return drawLine(p2.x, p2.y, interTo.x, interTo.y, color);
    }
};

checkLineRectIntersection = function(l1_sx, l1_sy, l1_ex, l1_ey, r_x, r_y, r_w, r_h) {
    var res;
    res = checkLineIntersection(l1_sx, l1_sy, l1_ex, l1_ey, r_x, r_y, r_x + r_w - 1, r_y);
    if (res.onLine1 && res.onLine2) {
        return res;
    }
    res = checkLineIntersection(l1_sx, l1_sy, l1_ex, l1_ey, r_x + r_w - 1, r_y, r_x + r_w - 1, r_y + r_h - 1);
    if (res.onLine1 && res.onLine2) {
        return res;
    }
    res = checkLineIntersection(l1_sx, l1_sy, l1_ex, l1_ey, r_x + r_w - 1, r_y + r_h - 1, r_x, r_y + r_h - 1);
    if (res.onLine1 && res.onLine2) {
        return res;
    }
    res = checkLineIntersection(l1_sx, l1_sy, l1_ex, l1_ey, r_x, r_y + r_h - 1, r_x, r_y);
    if (res.onLine1 && res.onLine2) {
        return res;
    }
    return null;
};

checkLineIntersection = function(l1_sx, l1_sy, l1_ex, l1_ey, l2_sx, l2_sy, l2_ex, l2_ey) {
    var a, b, denominator, numerator1, numerator2, result;
    result = {
        x: null,
        y: null,
        onLine1: false,
        onLine2: false
    };
    denominator = ((l2_ey - l2_sy) * (l1_ex - l1_sx)) - ((l2_ex - l2_sx) * (l1_ey - l1_sy));
    if (denominator === 0) {
        return result;
    }
    a = l1_sy - l2_sy;
    b = l1_sx - l2_sx;
    numerator1 = ((l2_ex - l2_sx) * a) - ((l2_ey - l2_sy) * b);
    numerator2 = ((l1_ex - l1_sx) * a) - ((l1_ey - l1_sy) * b);
    a = numerator1 / denominator;
    b = numerator2 / denominator;
    result.x = l1_sx + (a * (l1_ex - l1_sx));
    result.y = l1_sy + (a * (l1_ey - l1_sy));
    if (a > 0 && a < 1) {
        result.onLine1 = true;
    }
    if (b > 0 && b < 1) {
        result.onLine2 = true;
    }
    return result;
};

getArrowPos = function(x1, y1, x2, y2, ang, sz) {
    var l, result, xd, yd;
    result = {
        x: null,
        y: null
    };
    ang = Math.PI / 180 * ang;
    l = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
    xd = (x2 - x1) / l;
    yd = (y2 - y1) / l;
    result.x = x1 + (Math.cos(ang) * xd - Math.sin(ang) * yd) * sz;
    result.y = y1 + (Math.sin(ang) * xd + Math.cos(ang) * yd) * sz;
    return result;
};

isOnLine = function(x1, y1, x2, y2, px, py) {
    var f1, f2;
    f1 = function(somex) {
        return (y2 - y1) / (x2 - x1) * (somex - x1) + y1;
    };
    f2 = function(somey) {
        return (x2 - x1) / (y2 - y1) * (somey - y1) + x1;
    };
    return (Math.abs(f1(px) - py) < 10 && (px >= x1 && px <= x2 || px >= x2 && px <= x1)) || Math.abs(f2(py) - px) < 10 && (py >= y1 && py <= y2 || py >= y2 && py <= y1);
};