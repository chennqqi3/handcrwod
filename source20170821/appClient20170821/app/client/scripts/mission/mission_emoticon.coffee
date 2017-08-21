'use strict'

angular.module('app.mission.emoticon', [])

.controller('missionEmoticonCtrl', 
    ($rootScope, $scope, $api, $modalInstance, filterFilter, missionStorage, 
        logger, $session, $dialogs, $timeout, $location, CONFIG) ->
        # Close dialog
        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        $scope.is_custom = (em) ->
            return em.home_id != null

        # Upload background image for process
        $scope.onUpload = (files, type) ->
            if files.length == 0
                return
            file = files[0]
            missionStorage.upload_emoticon($scope.cur_mission.mission_id, file).progress( (evt) ->
                #console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total))
            ).success( (data, status, headers, config) ->
                if data.err_code == 0
                    $scope.emoticon.image = CONFIG.BASE + data.image
                    angular.element("input[type='file']").val('')
                else
                    logger.logError(data.err_msg)
            )

        $scope.change_alt = () ->
            alt = $scope.emoticon.alt
            if !$api.is_empty(alt) && alt.length > 0
                if alt[0] != ':'
                    alt = ':' + alt
                if alt[alt.length - 1] != ';'
                    alt = alt + ';'

                $scope.emoticon.alt = alt

        $scope.canUpdate = ->
            return $scope.form_emoticon.$valid && !$api.is_empty($scope.emoticon.image)

        $scope.edit = (em) ->
            if $scope.emoticon == em
                $scope.init()
            else
                $scope.emoticon = em
                $scope.emoticon.mission_id = $rootScope.cur_mission.mission_id

        $scope.add = () ->
            missionStorage.add_emoticon($scope.emoticon, (res) ->
                if (res.err_code == 0)
                    $api.init_emoticon(res.emoticon)
                    $rootScope.emoticons.push(res.emoticon)
                    $scope.init()
                else
                    logger.logError(res.err_msg)
                $scope.cancel()
            )

        $scope.save = () ->
            missionStorage.save_emoticon($scope.emoticon, (res) ->
                if (res.err_code == 0)
                    $api.init_emoticon(res.emoticon)
                    if $rootScope.emoticons
                        for i in [0..$rootScope.emoticons.length - 1]
                            if $rootScope.emoticons[i].emoticon_id == res.emoticon.emoticon_id        
                                $rootScope.emoticons[i] = res.emoticon
                    $scope.init()
                else
                    logger.logError(res.err_msg)
                $scope.cancel()
            )

        $scope.remove = () ->
            emoticon_id = $scope.emoticon.emoticon_id
            missionStorage.remove_emoticon(emoticon_id, (res) ->
                if (res.err_code == 0)
                    if $rootScope.emoticons
                        for i in [0..$rootScope.emoticons.length - 1]
                            if $rootScope.emoticons[i].emoticon_id == emoticon_id  
                                $rootScope.emoticons.splice(i, 1)
                                break
                    $scope.init()
                else
                    logger.logError(res.err_msg)
                $scope.cancel()
            )


        $scope.init = ->
            $scope.emoticon =
                mission_id: $rootScope.cur_mission.mission_id
                alt: ''
                title: ''

        $scope.init()

        return
)
