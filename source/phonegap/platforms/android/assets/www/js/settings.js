angular.module('app.settings', [])

.controller('SettingsCtrl', 
    function($scope, $api, $rootScope, logger, $session, $upload, 
        CONFIG, $location, $numutil, $timeout, $ionicModal, userStorage, taskStorage) {
        $scope.init = function() {
            var i, results;
            $scope.time_zones = moment.tz.names();
            $scope.alarm_times = (function() {
                results = [];
                for (i = 0; i <= 23; i++){ results.push(i); }
                return results;
            }).apply(this);

            userStorage.get_profile(null, function(res) {
                if (res.err_code === 0) {
                    $scope.user = res.user;
                    $scope.user.alarm_mail_flag = $scope.user.alarm_mail_flag == 1;
                }
            });

            $api.call("google/is_connected").then(function(res) {
                var url;
                if (res.data.err_code !== 0) {
                    url = $location.absUrl();
                    url = url.replace(/(#[\/\w]*)/gi, '') + "#/settings";
                    return $scope.google_auth_url = CONFIG.GOOGLE_CONNECT_URL + "?TOKEN=" + $session.getTOKEN() + "&redirect_url=" + encodeURIComponent(url);
                } else {
                    return $scope.google_auth_url = '';
                }
            });
        };

        $scope.changeHourlyAmount = function() {
            var v;
            v = $numutil.to_num($scope.user.hourly_amount);
            if (v < 0) {
                v = 0;
            }
            return $timeout(function() {
                return $scope.user.hourly_amount = v;
            });
        };

        $scope.canUpdateProfile = function() {
            return $scope.form_update_profile.$valid;
        };

        $scope.updateProfile = function() {
            var hourly_amount;
            hourly_amount = $numutil.to_num($scope.user.hourly_amount);
            userStorage.update_profile({
                user_name: $scope.user.user_name,
                email: $scope.user.email,
                skills: $scope.user.skills,
                hourly_amount: hourly_amount,
                time_zone: $scope.user.time_zone,
                alarm_mail_flag: $scope.user.alarm_mail_flag,
                alarm_time: $scope.user.alarm_time
            },  function(res) {
                if (res.err_code === 0) {
                    $session.user_name = $scope.user.user_name;
                    $session.email = $scope.user.email;
                    $session.time_zone = $scope.user.time_zone;
                    logger.logSuccess('プロファイルが保存されました。');
                } else {
                    logger.logError(res.err_msg);
                }
                $scope.showMessage = true;
            });
        };

        $scope.canUpdatePassword = function() {
            return $scope.form_update_password.$valid;
        };

        $scope.updatePassword = function() {
            userStorage.update_profile({
                old_password: $scope.user.old_password,
                new_password: $scope.user.new_password
            }, function(res) {
                if (data.err_code === 0) {
                    logger.logSuccess('パスワードが変更されました。');
                } else {
                    logger.logError(data.err_msg);
                }
                $scope.showMessage = true;
            });
        };

        $scope.onUploadAvartar = function(files) {
            var file;
            file = files[0];
            return userStorage.upload_avartar(file).progress(function(evt) {}).success(function(data, status, headers, config) {
                if (data.err_code === 0) {
                    return $scope.user.avartar = data.avartar;
                } else {
                    return logger.logError(data.err_msg);
                }
            });
        };

        $scope.disconnectGoogle = function() {
            return $api.call("google/disconnect").then(function(res) {
                var url;
                if (res.data.err_code === 0) {
                    url = $location.absUrl();
                    url = url.replace(/(#[\/\w]*)/gi, '') + "#/settings";
                    return $scope.google_auth_url = CONFIG.GOOGLE_CONNECT_URL + "?TOKEN=" + $session.getTOKEN() + "&redirect_url=" + encodeURIComponent(url);
                }
            });
        };

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
                    for (var i = 0; i < $scope.user.skills.length; i ++) {
                        var skill = $scope.user.skills[i];
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
            $scope.user.skills = skills;
            $scope.modalSkill.hide();
        }

        $scope.$on('reload_session', function() {
            return $scope.init();
        });

        // modal event
        $scope.$on('$destroy', function() {
            $scope.modalSkill.remove();
        });

        $scope.$on('$ionicView.beforeEnter', function(){
            $scope.init();
        });
        
    }
)