'use strict'

angular.module('app.mission.attach', [])

.controller('missionAttachCtrl', 
    ($scope, $api, taskStorage, missionStorage, chatStorage, filterFilter, $rootScope, $routeParams, logger, $session, $dateutil, $timeout, $dialogs, CONFIG) ->
        $scope.sync = ->
            $scope.mission_id = if !$api.is_empty($rootScope.cur_mission) then $rootScope.cur_mission.mission_id else null
            if $scope.mission_id  != null
                missionStorage.attaches($scope.mission_id, (res) ->
                    if res.err_code == 0
                        $scope.attaches = res.attaches
                )            

        $scope.sync()

        $scope.$on("synced-server", ->
            $scope.sync()

            return
        )

        # Upload files
        $scope.onUploadFiles = (files) ->
            if $api.is_empty($scope.files) || $scope.files.length == 0
                $scope.files = files
            else
                $scope.files = $scope.files.concat(files)

            angular.forEach files, (file) ->
                file.progress = 0
                size = Math.round(file.size  * 100 / (1024 * 1024)) / 100
                file.file_size = size

                upload = chatStorage.upload_file($scope.mission_id, file).progress( (evt) ->
                    file.progress = parseInt(100.0 * evt.loaded / evt.total)
                ).success( (data, status, headers, config) ->
                    i = $scope.files.indexOf(file)
                    $scope.files.splice(i, 1)
                    if data.err_code == 0
                        attach_url = CONFIG.API_BASE + data.mission_attach_url
                        attach =
                            mission_attach_id: data.mission_attach_id
                            create_time: data.create_time
                            file_name: file.name
                            attach_url: attach_url
                            file_size: size
                        $scope.attaches.unshift(attach)
                    else
                        logger.logError(data.err_msg)
                )
                file.upload = upload
                return

        $scope.onCancelUpload = (file) ->
            if file.upload
                $api.cancel_upload(file.upload)
                i = $scope.files.indexOf(file)
                $scope.files.splice(i, 1)
            return

        $scope.remove = (attach) ->
            missionStorage.remove_attach($scope.mission_id, attach.mission_attach_id, (res) ->
                    if res.err_code == 0
                        i = $scope.attaches.indexOf(attach)
                        $scope.attaches.splice(i, 1)
                )

)