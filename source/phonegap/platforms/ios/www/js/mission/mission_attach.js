'use strict'

angular.module('app.mission.attach', [])

.controller('missionAttachCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, 
        $ionicModal, $session, missionStorage, chatStorage, $timeout, logger, CONFIG) {

        $scope.init = function() {
            $scope.mission_id = parseInt($stateParams.mission_id, 10);
            missionStorage.attaches($scope.mission_id, function(res) {
                if (res.err_code == 0)
                    $scope.attaches = res.attaches;
            });
        }

        $scope.init();

        $scope.onUploadFiles = function(files) {
            if($api.is_empty($scope.files) || $scope.files.length == 0)
                $scope.files = files;
            else
                $scope.files = $scope.files.concat(files);

            return angular.forEach(files, function(file) {
                var size, upload;
                file.progress = 0;
                size = Math.round(file.size * 100 / (1024 * 1024)) / 100;
                file.file_size = size;
                upload = chatStorage.upload_file($scope.mission_id, file).progress(function(evt) {
                    return file.progress = parseInt(100.0 * evt.loaded / evt.total);
                }).success(function(data, status, headers, config) {
                    var attach, attach_url, i;
                    i = $scope.files.indexOf(file);
                    $scope.files.splice(i, 1);
                    if (data.err_code === 0) {
                        attach_url = CONFIG.API_BASE + data.mission_attach_url;
                        attach = {
                            mission_attach_id: data.mission_attach_id,
                            create_time: data.create_time,
                            file_name: file.name,
                            attach_url: attach_url,
                            file_size: size
                        };
                        return $scope.attaches.unshift(attach);
                    } else {
                        return logger.logError(data.err_msg);
                    }
                });
                file.upload = upload;
            });
        };

        $scope.onCancelUpload = function(file) {
            var i;
            if (file.upload) {
                $api.cancel_upload(file.upload);
                i = $scope.files.indexOf(file);
                $scope.files.splice(i, 1);
            }
        };

        $scope.remove = function(attach) {
            return missionStorage.remove_attach($scope.mission_id, attach.mission_attach_id, function(res) {
                var i;
                if (res.err_code === 0) {
                    i = $scope.attaches.indexOf(attach);
                    return $scope.attaches.splice(i, 1);
                }
            });
        };        
    }
);
