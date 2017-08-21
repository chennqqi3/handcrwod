'use strict'

angular.module('app.task.edit', [])

.controller('taskEditCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, 
        $ionicModal, $session, taskStorage, $timeout, logger) {
        $scope.max_level = 5;
        $scope.level_readonly = false;
        $scope.enable_start_date = true;
        $scope.enable_end_date = true;
        
        $scope.show_search_skill = false;
        $scope.search_skill = { text: '' };

        $scope.show_search_performer = false;
        $scope.search_performer = { text: '' };

        /* setting of date&time picker */
        $scope.dpStartDate = {
            inputDate: new Date(),
            titleLabel: '日付選択',  //Optional
            todayLabel: '今日',  //Optional
            closeLabel: '取消',  //Optional
            setLabel: '設定',  //Optional
            setButtonType : 'button-positive',  //Optional
            todayButtonType : 'button',  //Optional
            closeButtonType : 'button',  //Optional
            mondayFirst: true,    //Optional
            weekDaysList: ["日", "月", "火", "水", "木", "金", "土"],   //Optional
            monthList: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"], //Optional
            templateType: 'popup', //Optional
            showTodayButton: 'true', //Optional
            modalHeaderColor: 'bar-positive', //Optional
            modalFooterColor: 'bar-positive', //Optional
            callback: function (val) {    //Mandatory
                if (typeof(val) === 'undefined') {
                    $scope.task.plan_start_date = null;
                } else {
                    $scope.task.plan_start_date = val;
                }

                $scope.refreshStartDate(true);
            }
        };

        $scope.tpStartDate = {
            inputEpochTime: ((new Date()).getHours() * 60 * 60),  //Optional
            step: 15,  //Optional
            format: 24,  //Optional
            titleLabel: '時間選択',  //Optional
            setLabel: '設定',  //Optional
            closeLabel: '取消',  //Optional
            setButtonType: 'button-positive',  //Optional
            closeButtonType: 'button-stable',  //Optional
            callback: function (val) {    //Mandatory
                if (typeof(val) === 'undefined') {
                    $scope.task.plan_start_time = null;
                } else {
                    var tm = new Date(val * 1000);
                    var time = moment(tm.getUTCHours() + ":" + tm.getUTCMinutes(), "H:m").format('HH:mm:00');
                    var dt = moment.tz($scope.task.plan_start_date, $session.time_zone);
                    $scope.task.plan_start_time = dt.format("YYYY-MM-DD") + " " + time;
                }
                $scope.tpStartDate.inputEpochTime = val;
            }
        };

        $scope.dpEndDate = {
            inputDate: new Date(),
            titleLabel: '日付選択',  //Optional
            todayLabel: '今日',  //Optional
            closeLabel: '取消',  //Optional
            setLabel: '設定',  //Optional
            setButtonType : 'button-positive',  //Optional
            todayButtonType : 'button',  //Optional
            closeButtonType : 'button',  //Optional
            mondayFirst: true,    //Optional
            weekDaysList: ["日", "月", "火", "水", "木", "金", "土"],   //Optional
            monthList: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"], //Optional
            templateType: 'popup', //Optional
            showTodayButton: 'true', //Optional
            modalHeaderColor: 'bar-positive', //Optional
            modalFooterColor: 'bar-positive', //Optional
            callback: function (val) {    //Mandatory
                if (typeof(val) === 'undefined') {
                    $scope.task.plan_end_date = null;
                } else {
                    $scope.task.plan_end_date = val;
                }

                $scope.refreshEndDate(true);
            }
        };

        $scope.tpEndDate = {
            inputEpochTime: ((new Date()).getHours() * 60 * 60),  //Optional
            step: 15,  //Optional
            format: 24,  //Optional
            titleLabel: '時間選択',  //Optional
            setLabel: '設定',  //Optional
            closeLabel: '取消',  //Optional
            setButtonType: 'button-positive',  //Optional
            closeButtonType: 'button-stable',  //Optional
            callback: function (val) {    //Mandatory
                if (typeof(val) === 'undefined') {
                    $scope.task.plan_end_time = null;
                } else {
                    var tm = new Date(val * 1000);
                    var time = moment(tm.getUTCHours() + ":" + tm.getUTCMinutes(), "H:m").format('HH:mm:00');
                    var dt = moment.tz($scope.task.plan_end_date, $session.time_zone);
                    $scope.task.plan_end_time = dt.format("YYYY-MM-DD") + " " + time;
                }
                $scope.tpEndDate.inputEpochTime = val;
            }
        };

        $scope.refreshStartDate = function (reload) {
            if ($scope.task.plan_start_date != null)
            {
                var dt = moment.tz($scope.task.plan_start_date, $session.time_zone)
                $scope.dpStartDate.inputDate = new Date(dt.format("YYYY-MM-DD"));
                $scope.dpEndDate.from = $scope.dpStartDate.inputDate;

                if ($scope.task.plan_start_time != null) {
                    dt = moment.tz($scope.task.plan_start_time, $session.time_zone);
                    $scope.tpStartDate.inputEpochTime = dt.format('H') * 60 * 60 + dt.format('m') * 60
                }

                if (reload) {
                    $scope.enable_end_date = false;
                    $timeout(function() {
                        $scope.enable_end_date = true;
                    });
                }
            }
        }
        $scope.refreshEndDate = function (reload) {
            if ($scope.task.plan_end_date != null)
            {
                var dt = moment.tz($scope.task.plan_end_date, $session.time_zone)
                $scope.dpEndDate.inputDate = new Date(dt.format("YYYY-MM-DD"));
                $scope.dpStartDate.to = $scope.dpEndDate.inputDate;

                if ($scope.task.plan_end_time != null) {
                    dt = moment.tz($scope.task.plan_end_time, $session.time_zone);
                    $scope.tpEndDate.inputEpochTime = dt.format('H') * 60 * 60 + dt.format('m') * 60
                }

                if (reload) {
                    $scope.enable_start_date = false;
                    $timeout(function() {
                        $scope.enable_start_date = true;
                    });
                }
            }
        }

        $scope.init = function() {
            $scope.mission_id = parseInt($stateParams.mission_id, 10);
            $scope.task_id = parseInt($stateParams.task_id, 10);
            $scope.task = taskStorage.get_task($scope.task_id);

            if ($scope.task != null) {
                if ($scope.task.plan_hours != null)
                    $scope.task.plan_hours = parseInt($scope.task.plan_hours, 10);
                $scope.refreshStartDate();
                $scope.refreshEndDate();

                if ($scope.task.progress == null)
                    $scope.task.progress = 0;
                if ($scope.task.level == null)
                    $scope.task.level = 0;
                taskStorage.get_skills($scope.task.task_id, function(res) {
                    if (res.err_code == 0)
                        $scope.task.skills = res.skills;
                    else
                        $scope.task.skills = [];
                });
            }
        }

        $scope.checkPriority = function() {
            if ($scope.task.priority)
                $scope.task.priority = false;
            else
                $scope.task.priority = true;
        }

        // select performer
        $ionicModal.fromTemplateUrl('templates/task/sel_performer.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalPerformer = modal;
        });

        $scope.open_performer = function() {
            taskStorage.get_candidates($scope.task.task_id, function(res) {
                if (res.err_code == 0) {
                    $scope.users = res.users;
                    $scope.modalPerformer.show();
                }
                else
                    logger.logError(res.err_msg);
                return;
            });
        }

        $scope.close_performer = function() {
            $scope.modalPerformer.hide();
        }

        $scope.select_performer = function(user) {
            $scope.task.performer_id = user.user_id;
            $scope.task.performer_name = user.user_name;
            $scope.task.avartar = user.avartar;
            $scope.modalPerformer.hide();
        }

        // search
        $scope.open_search_performer = function() {
            $scope.show_search_performer = true;
        }
        $scope.close_search_performer = function() {
            $scope.show_search_performer = false;
            $scope.search_performer.text = '';
        }

        // select skill
        $ionicModal.fromTemplateUrl('templates/task/sel_skill.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalSkill = modal;
        });

        $scope.open_skill = function() {
            taskStorage.all_skills(function(res) {
                var skills = [];
                $scope.all_skills = [];
                if (res.err_code == 0)
                    skills = res.skills;
                skills.forEach(function (askill) {
                    var selected = false;
                    for (var i = 0; i < $scope.task.skills.length; i ++) {
                        var skill = $scope.task.skills[i];
                        if (askill == skill) {
                            selected = true;
                            break;
                        }
                    }
                    $scope.all_skills.push({ selected: selected, skill_name: askill });
                });
                $scope.modalSkill.show();
            });
        }

        $scope.close_skill = function() {
            var skills = [];
            $scope.all_skills.forEach(function(skill) {
                if (skill.selected)
                    skills.push(skill.skill_name);
            });
            $scope.task.skills = skills;
            $scope.modalSkill.hide();
        }

        $scope.select_skill = function(user) {
            $scope.modalSkill.hide();
        }

        // search
        $scope.open_search_skill = function() {
            $scope.show_search_skill = true;
        }
        $scope.close_search_skill = function() {
            $scope.show_search_skill = false;
            $scope.search_skill.text = '';
        }

        // modal event
        $scope.$on('$destroy', function() {
            $scope.modalPerformer.remove();
            $scope.modalSkill.remove();

            taskStorage.edit($scope.task, function(res) {
                if (res.err_code == 0) {
                    $scope.task.complete_flag = res.complete_flag == 1
                    for (var i = 0; i < $scope.tasks.length; i ++) {
                        if ($scope.tasks[i].task_id == $scope.task.task_id)
                        {
                            $scope.tasks[i] = $scope.task;
                            break;
                        }
                    }
                }
            });
        });
        $scope.$on('modal.hidden', function() {
            // Execute action
        });
        $scope.$on('modal.removed', function() {
            // Execute action
        });

        $scope.canProgress = function() {
            return $scope.task != null && ($rootScope.canEditTask() || $scope.task.performer_id == $session.user_id);            
        }

        $scope.init();
    }
);
