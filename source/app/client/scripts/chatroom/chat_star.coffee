'use strict'

angular.module('app.chat.star', [])

.controller('chatStarCtrl', 
    ($scope, $api, $location, chatStorage, missionStorage, filterFilter, $rootScope, $routeParams, logger, $session, $dateutil, $timeout, $dialogs, CONFIG) -> 
        MAX_MSG_LENGTH = 100

        # Initialize
        $scope.sync = () ->
            if $rootScope.cur_mission != null && $session.user_id != null 
                $scope.mission_id = $rootScope.cur_mission.mission_id

                chatStorage.star_messages()
                    .then((messages) ->
                        $scope.messages = messages
                        length = $scope.messages.length
                        if length > 0
                            $scope.last_cid = $scope.messages[length-1].cmsg_id
                        else
                            $scope.last_cid = null

                        $scope.initPreviewLink()
                    )

        $scope.prev = () ->
            if $scope.messages
                prev_id = $scope.messages[0].cmsg_id

                chatStorage.star_messages(prev_id, null)
                    .then((messages) ->
                        if messages.length > 0
                            $scope.messages[0].date_label = $dateutil.ellipsis_time_str($scope.messages[0].date, messages[0].date)
                            for i in [0..messages.length - 1]
                                $scope.messages.splice(i, 0, messages[i])
                            
                            if $scope.messages.length > MAX_MSG_LENGTH
                                $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length-MAX_MSG_LENGTH)

                            $scope.startScrollTimer(prev_id, "prev")

                            $scope.initPreviewLink()

                            return messages
                        else
                            return []
                    )

        $scope.next = () ->
            if $scope.messages
                length = $scope.messages.length
                next_id = $scope.messages[length-1].cmsg_id

                chatStorage.star_messages(null, next_id)
                    .then((messages) ->
                        if messages.length > 0
                            messages[0].date_label = $dateutil.ellipsis_time_str(messages[0].date, $scope.messages[length-1].date)
                            for i in [0..messages.length - 1]
                                $scope.messages.push(messages[i])
                            
                            if $scope.messages.length > MAX_MSG_LENGTH
                                $scope.messages.splice(0, $scope.messages.length-MAX_MSG_LENGTH)

                            $scope.startScrollTimer(next_id, "next")

                            $scope.initPreviewLink()

                            return messages
                        else
                            return []
                    )

        $scope.sync()

        $scope.$on('refresh-star', ->
            $scope.sync()
        )

        $scope.initPreviewLink = ->
            $timeout(->
                $('.preview-image').off('click')
                $('.preview-image').on('click', ->
                    url = $(this).attr('preview-image')
                    $dialogs.previewImage(url)
                )
            , 2000)

        $scope.startScrollTimer = (cmsg_id, type) ->
            if $scope.scrollTimer != null
                $scope.stopScrollTimer()                
            $scope.scrollTimer = $timeout(->
                scrollView = angular.element('#chat_star_view')
                elem = angular.element('#chat_star_' + cmsg_id)
                if(elem && elem[0])
                    rect = elem[0].getBoundingClientRect()
                    if(type == "prev")
                        scrollOffset = 0
                    else if(type == "next")
                        scrollOffset = scrollView.outerHeight()
                        if(rect)
                            scrollOffset -= rect.height
                    else
                        scrollOffset = 0
                    scrollView.duScrollToElement(angular.element('#chat_star_' + cmsg_id), scrollOffset)

                $scope.stopScrollTimer()
            )

        $scope.stopScrollTimer = () ->
            if $scope.scrollTimer != null
                $timeout.cancel($scope.scrollTimer)
                $scope.scrollTimer = null

        $scope.goMessage = (message) ->
            if $rootScope.cur_mission != null && $rootScope.cur_mission.mission_id == message.mission_id
                $rootScope.$broadcast('scroll-to-message', message.cmsg_id)
            else
                $location.path("/chats/" + message.mission_id + "/" + message.cmsg_id)
            return

        $scope.unstar = (message) ->
            message.star = false
            
            chatStorage.star_message(message.cmsg_id, message.star)
            $rootScope.$broadcast('unstar-message', message)

            return
        
        # check to
        $scope.isToMine = (message) ->
            if $api.is_empty(message.content)
                return false

            mine = false
            message.content.replace(/\[to:([^\]]*)\]/g, (item, user_id) ->
                if $session.user_id + '' == user_id
                    mine = true
            )

            return mine

)