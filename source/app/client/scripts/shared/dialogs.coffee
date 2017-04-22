angular.module('app.dialogs', [])

.service('$dialogs', 
    ($rootScope, logger, $modal) ->
        $this = this

        this.confirm = (title, message, ok_label, callback, param, ok_class) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_confirm.html' + $rootScope.ver
                controller: 'modalConfirmCtrl'
                resolve:
                    title: ->
                        return title
                    message: ->
                        return message
                    ok_label: ->
                        return ok_label
                    ok_class: ->
                        return ok_class
            )

            modalInstance.result.then(
                (ret) ->
                    getType = {}
                    if callback && getType.toString.call(callback) == '[object Function]'
                        callback(param)
                    else
                        $rootScope.$broadcast(callback, param)
                , ->
            )

        this.selPerformer = (task) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_performer.html' + $rootScope.ver
                controller: 'modalPerformerCtrl'
                resolve:
                    task: ->
                        return task
            )

        this.addMissionMember = (mission, search_string) ->
            modalInstance = $modal.open(
                templateUrl: 'views/mission/mission_member_add.html' + $rootScope.ver
                controller: 'missionMemberAddCtrl'
                resolve:
                    mission: ->
                        return mission
                    search_string: ->
                        return search_string
            )

        this.addTask = (mission, task_name) ->
            modalInstance = $modal.open(
                templateUrl: 'views/task/task_add.html' + $rootScope.ver
                controller: 'modalAddTaskCtrl'
                resolve:
                    mission: ->
                        return mission
                    task_name: ->
                        return task_name
            )

        this.showContract = (title, content) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_contract.html' + $rootScope.ver
                controller: 'modalContractCtrl'
                resolve:
                    title: ->
                        return title
                    content: ->
                        return content
            )

        this.settingBackImage = (mission, back_type) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_back_image.html' + $rootScope.ver
                controller: 'modalBackImageCtrl'
                resolve:
                    mission: ->
                        return mission
                    back_type: ->
                        return back_type
            )

        this.uploadAttach = (type, object_id) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_upload.html' + $rootScope.ver
                controller: 'modalUploadCtrl'
                resolve:
                    type: ->
                        return type
                    object_id: ->
                        return object_id
            )

        # Home related
        this.addHome = () ->
            modalInstance = $modal.open(
                templateUrl: 'views/home/home_add.html' + $rootScope.ver
                controller: 'homeAddCtrl'
            )

        this.inviteHome = (home, email) ->
            modalInstance = $modal.open(
                templateUrl: 'views/home/home_invite.html' + $rootScope.ver
                controller: 'homeInviteCtrl'
                resolve:
                    home: ->
                        return home
                    email: ->
                        return email
            )

        this.editHome = (home) ->
            modalInstance = $modal.open(
                templateUrl: 'views/home/home_edit.html' + $rootScope.ver
                controller: 'homeEditCtrl'
                resolve:
                    home: ->
                        return home
            )

        this.selPriv = (priv, callback) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_sel_priv.html' + $rootScope.ver
                controller: 'selPrivCtrl'
                resolve:
                    priv: ->
                        return priv
                    callback: ->
                        return callback
            )

        this.selRoomPriv = (priv, callback) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_sel_rpriv.html' + $rootScope.ver
                controller: 'selRPrivCtrl'
                resolve:
                    priv: ->
                        return priv
                    callback: ->
                        return callback
            )

        this.importCSV = (home_id) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_import_csv.html' + $rootScope.ver
                controller: 'importCSVCtrl'
                resolve:
                    home_id: ->
                        return home_id
            )

        # Mission related
        this.addMission = () ->
            modalInstance = $modal.open(
                templateUrl: 'views/mission/mission_add.html' + $rootScope.ver
                controller: 'missionAddCtrl'
            )

        this.openMission = (private_flag) ->
            if private_flag == 2
                modalInstance = $modal.open(
                    templateUrl: 'views/mission/mission_open_member.html' + $rootScope.ver
                    controller: 'missionOpenCtrl'
                    resolve:
                        private_flag: ->
                            return private_flag
                )
            else
                modalInstance = $modal.open(
                    templateUrl: 'views/mission/mission_open.html' + $rootScope.ver
                    controller: 'missionOpenCtrl'
                    resolve:
                        private_flag: ->
                            return private_flag
                )

        this.editMission = (mission) ->
            modalInstance = $modal.open(
                templateUrl: 'views/mission/mission_edit.html' + $rootScope.ver
                controller: 'missionEditCtrl'
                resolve:
                    mission: ->
                        return mission
            )

        this.memberMission = (mission) ->
            modalInstance = $modal.open(
                templateUrl: 'views/mission/mission_member.html' + $rootScope.ver
                controller: 'missionMemberCtrl'
                resolve:
                    mission: ->
                        return mission
            )

        this.reqEntrance = (task) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_req_entr.html' + $rootScope.ver
                controller: 'modalReqEntrCtrl'
                resolve:
                    task: ->
                        return task
            )

        this.helpEntrance = (task) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_help_entr.html' + $rootScope.ver
                controller: 'modalHelpEntrCtrl'
                resolve:
                    task: ->
                        return task
            )

        this.inviteMission = (mission, email) ->
            modalInstance = $modal.open(
                templateUrl: 'views/mission/mission_invite.html' + $rootScope.ver
                controller: 'missionInviteCtrl'
                resolve:
                    mission: ->
                        return mission
                    email: ->
                        return email
            )

        this.missionEmoticon = () ->
            modalInstance = $modal.open(
                templateUrl: 'views/mission/mission_emoticon.html' + $rootScope.ver
                controller: 'missionEmoticonCtrl'
            )

        this.chatSearch = (search_this_room, search_string, callback) ->
            modalInstance = $modal.open(
                templateUrl: 'views/chatroom/chat_search.html' + $rootScope.ver
                controller: 'chatMessageCtrl'
                size: 'lg'
                resolve:
                    search_this_room: ->
                        return search_this_room
                    search_string: ->
                        return search_string
                    callback: ->
                        return callback
            )

        # alert
        this.showAlerts = () ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_alerts.html' + $rootScope.ver
                controller: 'modalAlertsCtrl'
            )

        this.previewImage = (url, title, width, height) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_preview.html' + $rootScope.ver
                controller: 'modalPreviewImageCtrl'
                size: 'lg'
                resolve:
                    url: ->
                        return url
                    title: ->
                        return title
                    width: ->
                        return width
                    height: -> 
                        return height
            )

        this.previewVideo = (url) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_preview_video.html' + $rootScope.ver
                controller: 'modalPreviewVideoCtrl'
                size: 'lg'
                resolve:
                    url: ->
                        return url
            )

        # user profile
        this.showUserProfile = (user_id) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_profile.html' + $rootScope.ver
                controller: 'modalProfileCtrl'
                resolve:
                    user_id: ->
                        return user_id
            )

        this.showQR = (url, deep_link, title) ->
            modalInstance = $modal.open(
                templateUrl: 'views/dialogs/mdl_qr.html' + $rootScope.ver
                controller: 'modalShowQRCtrl'
                size: 'lg'
                resolve:
                    url: ->
                        return url
                    deep_link: ->
                        return deep_link
                    title: ->
                        return title
            )
        return this
)

.controller('modalConfirmCtrl', 
    ($scope, $rootScope, $modalInstance, title, message, ok_label, ok_class) ->

        $scope.title = title
        $scope.message = message
        $scope.ok_label = ok_label

        if ok_class == undefined
            $scope.ok_class = 'btn-warning'
        else
            $scope.ok_class = ok_class

        $scope.ok = ->
            $modalInstance.close('ok');

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')
)

.controller('modalPerformerCtrl', 
    ($scope, $rootScope, $modalInstance, $api, task, taskStorage) ->
        $scope.task = task
        $scope.max_level = 5
        $scope.level_readonly = true

        $scope.search = (task_id) ->
            taskStorage.get_candidates(task_id, (res) ->
                if res.err_code == 0
                    $scope.users = res.users
                else
                    logger.logError(res.err_msg)
                return
            )
            return

        $scope.selectPerformer = (user) ->
            $modalInstance.close('select');
            task.performer_id = user.user_id
            task.performer_name = user.user_name
            task.avartar = user.avartar
            taskStorage.edit({  task_id: task.task_id, mission_id: task.mission_id, performer_id: user.user_id }, (res) ->
                if data.err_code != 0
                    logger.logError(data.err_msg)
            )
            return

        $scope.complete = ->
            $modalInstance.close('complete');
            task.complete_flag = true
            $rootScope.$broadcast('complete_task', task)
            return

        $scope.uncomplete =  ->
            $modalInstance.close('uncomplete');
            task.complete_flag = false
            $rootScope.$broadcast('complete_task', task)
            return

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        $scope.search(task.task_id)
)

.controller('modalAddTaskCtrl', 
    ($scope, $rootScope, $modalInstance, $api, mission, task_name, logger, taskStorage, chatizeService) ->
        $scope.mission = mission
        $scope.posting = false
        $scope.task = 
            mission_id: mission.mission_id
            task_name: chatizeService.strip(task_name)

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        $scope.ok = ->
            if $scope.task.task_name != ""
                $scope.posting = true
                taskStorage.add($scope.task, (res) ->
                    $scope.posting = false
                    if res.err_code == 0
                        $modalInstance.close('ok');
                        $rootScope.$broadcast('refresh-tasks')  
                        logger.logSuccess('新しいタスクが登録されました。')
                    else
                        logger.logError(res.err_msg)
                )
            return
)

.controller('modalReqEntrCtrl', 
    ($scope, $rootScope, $modalInstance, $api, task, logger, $timeout, $session, $dateutil, numberFilter) ->
        content = "下記のタスクの見積もり依頼します。\n" +
            "（※有償プランの場合、1～2営業日以内に担当者よりご連絡します。）\n" +
            "タスク名:" + task.task_name + "\n"

        if task.summary != null
            content = content + "概要:\n" + task.summary + "\n"

        content = content + "開始予定日付: " 
        if task.plan_start_date != null
            if task.plan_start_time != null
                content = content + $dateutil.date_time_label(task.plan_start_time)
            else
                content = content + $dateutil.date_label(task.plan_start_date)
        else
            content = content + " - "
        content = content + "\n"
        content = content + "完了予定日付: "
        if task.plan_end_date != null
            if task.plan_end_time != null
                content = content + $dateutil.date_time_label(task.plan_end_time)
            else
                content = content + $dateutil.date_label(task.plan_end_date)
        else
            content = content + " - "
        content = content + "\n"
        content = content + "工数: "
        if task.plan_hours != null
            content = content + $dateutil.times_label(task.plan_hours)
        else
            content = content + " - "
        content = content + "\n"
        content = content + "予算: "
        if task.plan_budget != null
            content = content + numberFilter(task.plan_budget, 0) + "円"
        else
            content = content + " - "

        content = content + "\n\n" +
            "依頼主: " + $session.user_name + "\n"

        $rootScope.content = content
        $rootScope.req_task_id = task.task_id

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        $scope.ok = ->
            $timeout(->
                angular.element('#form_req_ent').trigger('submit')
            , 5)
        
        $scope.$on('request_entrance_ok', () ->
            $modalInstance.dismiss('ok')            
        )
)

.controller('frmReqEntrCtrl', 
    ($scope, $rootScope, $api, logger, taskStorage) ->
        $scope.req =
            title: "サポートチームにお見積依頼"
            content: $rootScope.content
            task_id: $rootScope.req_task_id

        $scope.submitForm = ->
            if $scope.form_req_ent.$valid
                taskStorage.request_entrance($scope.req, (res) ->
                    if res.err_code == 0
                        logger.logSuccess('外部への依頼が完了しました。')
                    else
                        logger.logError(res.err_msg)
                )
                $rootScope.$broadcast('request_entrance_ok')
            return
)

.controller('modalHelpEntrCtrl', 
    ($scope, $rootScope, $modalInstance, $api, task, logger, $timeout, $session) ->
        content = "下記のタスクについて確認をお願いします。\n" +
            "タスク名:" + task.task_name + "\n"

        if task.summary != null
            content = content + "概要:\n" + task.summary

        content = content + "\n\n" +
            $session.user_name + "(" + $session.email + ")\n" +
            "作成者"

        $rootScope.content = content
        $rootScope.help_task_id = task.task_id

        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        $scope.ok = ->
            $timeout(->
                angular.element('#form_help_ent').trigger('submit')
            , 5)
        
        $scope.$on('help_entrance_ok', () ->
            $modalInstance.dismiss('ok')            
        )
)

.controller('frmHelpEntrCtrl', 
    ($scope, $rootScope, $api, logger) ->
        $scope.req =
            email: ""
            title: "タスクのヘルプ"
            content: $rootScope.content
            task_id: $rootScope.help_task_id

        $scope.submitForm = ->
            if $scope.form_help_ent.$valid
                taskStorage.help_entrance($scope.req, (res) ->
                    if res.err_code == 0
                        logger.logSuccess('タスクの担当者たちへメールが届きました。')
                    else
                        logger.logError(res.err_msg)
                )
                $rootScope.$broadcast('help_entrance_ok')
            return
)

.controller('modalContractCtrl', 
    ($scope, $rootScope, $modalInstance, title, content) ->

        $scope.title = title
        $scope.content = content

        $scope.close = ->
            $modalInstance.dismiss('close')
)

.controller('modalBackImageCtrl', 
    ($scope, $rootScope, $modalInstance, $api, logger, mission, back_type, missionStorage) ->

        $scope.back_image = if back_type==0 then mission.job_back_url else mission.prc_back_url
        $scope.back_pos = if back_type==0 then mission.job_back_pos else mission.prc_back_pos
        $scope.back_pos_options = [
            (id:0, text:"画面に合わせて伸縮")
            (id:1, text:"並べて表示")
            (id:2, text:"中央に表示")
            (id:3, text:"そのまま表示")
        ]

        $scope.close = ->
            $modalInstance.dismiss('close')

        $scope.ok = ->
            missionStorage.set_back_pos(mission.mission_id, back_type, $scope.back_pos, (res) ->
                if res.err_code == 0
                    mission.job_back_pos = res.job_back_pos
                    mission.prc_back_pos = res.prc_back_pos
                    missionStorage.set_cur_mission(mission)
                    logger.logSuccess('配置情報を更新しました。')
                    $rootScope.$broadcast('refresh_back_image')

                    $scope.close()
                else
                    logger.logError(res.err_msg)
            )
            return

        $scope.delete = ->
            missionStorage.delete_back_image(mission.mission_id, back_type, (res) ->
                if res.err_code == 0
                    mission.job_back = res.job_back
                    mission.job_back_url = res.job_back_url
                    mission.prc_back = res.prc_back
                    mission.prc_back_url = res.prc_back_url
                    missionStorage.set_cur_mission(mission)
                    logger.logSuccess('背景画像を削除しました。')
                    $rootScope.$broadcast('refresh_back_image')

                    $scope.close()
                else
                    logger.logError(res.err_msg)
            )
            return
)

.controller('modalUploadCtrl', 
    ($scope, $rootScope, $modalInstance, $session, type, object_id) ->

        $scope.type = type
        $scope.object_id = object_id
        $scope.TOKEN = $session.getTOKEN()

        $scope.close = ->
            $modalInstance.dismiss('close')
            if type == 0
                $rootScope.$broadcast("refresh_mission_attach")
            else # type == 1
                $rootScope.$broadcast("refresh-comments")
)

.directive('uploadForm', 
    (CONFIG, logger, $api)->
        return (scope, element, attrs) ->
            if scope.type == 0
                url = CONFIG.API_BASE + "mission/upload_attach"
            else # type == 1
                url = CONFIG.API_BASE + "task/upload_attach"

            element.dropzone( 
                url: url
                maxFilesize: 500 # MB
                paramName: "file"
                maxThumbnailFilesize: 5
                init: ->
                    this.on('success', (file, json) ->
                        ret = JSON.parse(json)
                        if ret.err_code == 0
                            if scope.type == 0
                                file.object_id = ret.mission_attach_id
                            else # type == 1
                                file.object_id = ret.task_comment_id

                            logger.logSuccess('ファイルをアップロードしました。')
                        else
                            logger.logError(ret.err_msg)
                            file.object_id = null
                            this.removeFile(file)
                    )

                    this.on('addedfile', (file) ->
                        scope.$apply( ->
                        )
                    )
                  
                    this.on('drop', (file) ->
                        #alert('file')
                    )

                removedfile: (file)->
                    if file.object_id == null || file.object_id == undefined
                        _ref = file.previewElement
                        if _ref != null 
                            _ref.parentNode.removeChild(file.previewElement)
                        return

                    if scope.type == 0
                        url = "mission/delete_attach"
                        params = 
                            mission_id: scope.object_id
                            mission_attach_id: file.object_id
                    else # type == 1
                        url = "task/remove_comment"
                        params = 
                            task_comment_id: file.object_id

                     $api.call(url, params)
                        .success((data, status, headers, config) ->
                            if data.err_code == 0
                                logger.logSuccess('ファイルを削除しました。')
                                _ref = file.previewElement
                                if _ref != null 
                                    _ref.parentNode.removeChild(file.previewElement)
                            else
                                logger.logError(data.err_msg)
                        )

            )
            
)

.controller('selPrivCtrl', 
    ($scope, $rootScope, $modalInstance, $api, callback, priv) ->
        $scope.priv = priv

        $scope.ok = () ->
            callback($scope.priv)
            $scope.cancel()

        $scope.cancel = ->
            $modalInstance.dismiss('close')
)

.controller('selRPrivCtrl', 
    ($scope, $rootScope, $modalInstance, $api, callback, priv) ->
        $scope.priv = priv

        $scope.ok = () ->
            callback($scope.priv)
            $scope.cancel()

        $scope.cancel = ->
            $modalInstance.dismiss('close')
)

.controller('importCSVCtrl', 
    ($scope, $rootScope, $modalInstance, $api, $dialogs, home_id) ->
        $scope.home_id = home_id

        $scope.importCSV = (files)->
            if files.length == 0
                return
            file = files[0]

            message = "このCSVファイルを取り込みます。よろしいでしょうか？"
            $dialogs.confirm('CSVインポート', message, '確認', "import-csv", file)
            $scope.close()
            return

        $scope.close = ->
            $modalInstance.dismiss('close')
)

.controller('modalAlertsCtrl', 
    ($scope, $api, $modalInstance, filterFilter, $rootScope, logger, $session, homeStorage) ->
        $scope.posting = false

        # Close dialog
        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        $scope.accept = (alert, accept) ->
            $scope.posting = true
            homeStorage.accept_invite(alert.data.home_id, accept, (res) ->
                $scope.posting = false
                if res.err_code == 0
                    if accept == 1
                        logger.logSuccess(alert.data.home_name + "への招待が完了しました。")
                        $rootScope.$broadcast('refresh-homes')
                    else
                        logger.logSuccess(alert.data.home_name + "への招待を取消しました。")
                else if res.err_code == 61 || res.err_code == 63
                    logger.logError(res.err_msg)
                else
                    logger.logError(res.err_msg)
                    return

                for i in [0..$rootScope.alerts.length - 1]
                    if $rootScope.alerts[i].data.home_id == alert.data.home_id
                        $rootScope.alerts.splice(i, 1)
                        
                if $rootScope.alerts.length == 0
                    $scope.cancel()

                return
            )
            return

        return
)

.controller('modalPreviewImageCtrl', 
    ($scope, $rootScope, $modalInstance, $api, logger, url, title, width, height) ->
        if url
            len = url.length
            if url.substring(len-3) == 'gif'
                $scope.url = url
            else
                $scope.url = url + '/1000'
            $scope.org_url = url

        if $api.is_empty(title)
            $scope.title = "プレビュー"
        else
            $scope.title = title

        if width > 0 && height > 0
            if width > 800
                height = parseInt(height * 800 / width, 10)
                width = 800
            $scope.style = "height: " + height + "px; width: " + width + "px;"
        else 
            $scope.style = "min-height: 200px; max-width:800px;"

        $scope.close = ->
            $modalInstance.dismiss('close')
)

.controller('modalPreviewVideoCtrl', 
    ($scope, $rootScope, $modalInstance, $api, logger, url, $sce) ->

        $scope.url = $sce.trustAsResourceUrl(url);

        $scope.close = ->
            $modalInstance.dismiss('close')
)

.controller('modalProfileCtrl', 
    ($scope, $rootScope, $modalInstance, $api, logger, user_id, userStorage) ->

        $scope.user_id = user_id

        userStorage.get_profile(user_id, (res) ->
            if res.err_code == 0
                $scope.user = res.user
        )

        $scope.close = ->
            $modalInstance.dismiss('close')
)

.controller('modalShowQRCtrl', 
    ($scope, $rootScope, $modalInstance, $api, logger, url, deep_link, title) ->
        $scope.url = url
        $scope.qr_url = $api.qr_image_url(url, 300)
        if $api.is_empty(title)
            $scope.title = "QRコード"
        else
            $scope.title = title

        $scope.close = ->
            $modalInstance.dismiss('close')
)