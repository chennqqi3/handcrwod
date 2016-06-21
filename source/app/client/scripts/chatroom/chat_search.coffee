'use strict'

angular.module('app.chatroom.search', [])

.controller('chatMessageCtrl', 
    ($scope, $rootScope, $modalInstance, $api, search_string, search_this_room, 
        callback, $session, $dialogs, logger, chatStorage, $timeout) ->
        $scope.session = $session        
        $scope.req =
            search_this_room: search_this_room
            search_string: search_string

        $scope.messages = []
        $scope.prev_id = null
        $scope.next_id = null

        MAX_MSG_LENGTH = 100

        $scope.init = () ->
            $scope.messages = []

            $scope.prev_id = null
            $scope.next_id = null

        $scope.search = (init) ->
            if $api.is_empty($scope.req.search_string)
                return

            $scope.init() if init
                
            if $scope.req.search_this_room && !$api.is_empty($scope.cur_mission)
                mission_id = $rootScope.cur_mission.mission_id
            else
                mission_id = null

            chatStorage.search_messages($rootScope.cur_home.home_id, mission_id, $scope.req.search_string, $scope.prev_id, $scope.next_id)
                .then((messages) ->
                    if messages.length > 0
                        if $scope.next_id != null
                            for i in [0..messages.length - 1]
                                $scope.messages.push(messages[i])
                                if $scope.messages.length > MAX_MSG_LENGTH
                                    $scope.messages.splice(0, $scope.messages.length-MAX_MSG_LENGTH)
                                $scope.startScrollTimer($scope.next_id, "next")
                        else if $scope.prev_id != null
                            for i in [0..messages.length - 1]
                                $scope.messages.splice(i, 0, messages[i])                                
                                if $scope.messages.length > MAX_MSG_LENGTH
                                    $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length-MAX_MSG_LENGTH)
                                $scope.startScrollTimer($scope.prev_id, "prev")
                        else
                            $scope.messages = messages
                 )

        $scope.scrollTimer = null

        $scope.startScrollTimer = (cmsg_id, type) ->
            if $scope.scrollTimer != null
                $scope.stopScrollTimer()                
            $scope.scrollTimer = $timeout(->
                    scrollView = angular.element('#chat_search_view')
                    elem = angular.element('#chat_' + cmsg_id)
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
                        scrollView.duScrollToElement(angular.element('#chat_' + cmsg_id), scrollOffset)

                    $scope.stopScrollTimer()
                )

        $scope.stopScrollTimer = () ->
            if $scope.scrollTimer != null
                $timeout.cancel($scope.scrollTimer)
                $scope.scrollTimer = null

        $scope.prev = () ->
            if $scope.messages.length > 0
                $scope.prev_id = $scope.messages[0].cmsg_id
                $scope.next_id = null
                $scope.search(false)

        $scope.next = () ->
            length = $scope.messages.length
            if $scope.messages.length > 0
                $scope.prev_id = null
                $scope.next_id = $scope.messages[length-1].cmsg_id
                $scope.search(false)


        $scope.goMessage = (message) ->
            if callback
                callback(message)
            $modalInstance.dismiss('close')
            return

        ###
        $scope.quoteMessage = (message) ->
            if callback
                callback(2, message)
            $modalInstance.dismiss('close')            
            return
        ###

        $scope.cancel = ->
            $modalInstance.dismiss('close')

        $scope.close = ->
            $modalInstance.dismiss('close')

        $scope.search()
)
