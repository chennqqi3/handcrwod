angular.module('app.chat.star', [])

.controller('chatStarCtrl', 
    function($scope, $api, chatStorage, missionStorage, filterFilter, $rootScope, $routeParams, logger, $session, $dateutil, $timeout, CONFIG) {
        $scope.sync = function() {
            if ($rootScope.cur_mission !== null && $session.user_id !== null) {
                $scope.mission_id = $rootScope.cur_mission.mission_id;
                return chatStorage.messages(null, null, null, true).then(function(messages) {
                    var length;
                    $scope.messages = messages;
                    length = $scope.messages.length;
                    if (length > 0) {
                        $scope.last_cid = $scope.messages[length - 1].cmsg_id;
                    } else {
                        $scope.last_cid = null;
                    }
                    return $scope.initPreviewLink();
                });
            }
        };
        $scope.prev = function() {
            var prev_id;
            if ($scope.messages) {
                prev_id = $scope.messages[0].cmsg_id;
                return chatStorage.messages(null, prev_id, null, true).then(function(messages) {
                    var i, j, ref;
                    if (messages.length > 0) {
                        $scope.messages[0].date_label = $dateutil.ellipsis_time_str($scope.messages[0].date, messages[0].date);
                        for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                            $scope.messages.splice(i, 0, messages[i]);
                        }
                        if ($scope.messages.length > MAX_MSG_LENGTH) {
                            $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length - MAX_MSG_LENGTH);
                        }
                        $scope.startScrollTimer(prev_id, "prev");
                        $scope.initPreviewLink();
                        return messages;
                    } else {
                        return [];
                    }
                });
            }
        };
        $scope.next = function() {
            var length, next_id;
            if ($scope.messages) {
                length = $scope.messages.length;
                next_id = $scope.messages[length - 1].cmsg_id;
                return chatStorage.messages(null, null, next_id, true).then(function(messages) {
                    var i, j, ref;
                    if (messages.length > 0) {
                        messages[0].date_label = $dateutil.ellipsis_time_str(messages[0].date, $scope.messages[length - 1].date);
                        for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                            $scope.messages.push(messages[i]);
                        }
                        if ($scope.messages.length > MAX_MSG_LENGTH) {
                            $scope.messages.splice(0, $scope.messages.length - MAX_MSG_LENGTH);
                        }
                        $scope.startScrollTimer(next_id, "next");
                        console.log("next_id:" + next_id);
                        $scope.initPreviewLink();
                        return messages;
                    } else {
                        return [];
                    }
                });
            }
        };
        $scope.sync();
        $scope.initPreviewLink = function() {
            return $timeout(function() {
                $('.preview-image').off('click');
                return $('.preview-image').on('click', function() {
                    var url;
                    url = $(this).attr('preview-image');
                    return $dialogs.previewImage(url);
                });
            }, 2000);
        };
        $scope.startScrollTimer = function(cmsg_id, type) {
            if ($scope.scrollTimer !== null) {
                $scope.stopScrollTimer();
            }
            return $scope.scrollTimer = $timeout(function() {
                var elem, rect, scrollOffset, scrollView;
                scrollView = angular.element('#chat_star_view');
                elem = angular.element('#chat_star_' + cmsg_id);
                if (elem && elem[0]) {
                    rect = elem[0].getBoundingClientRect();
                    if (type === "prev") {
                        scrollOffset = 0;
                    } else if (type === "next") {
                        scrollOffset = scrollView.outerHeight();
                        if (rect) {
                            scrollOffset -= rect.height;
                        }
                    } else {
                        scrollOffset = 0;
                    }
                    scrollView.duScrollToElement(angular.element('#chat_star_' + cmsg_id), scrollOffset);
                }
                return $scope.stopScrollTimer();
            });
        };
        $scope.stopScrollTimer = function() {
            if ($scope.scrollTimer !== null) {
                $timeout.cancel($scope.scrollTimer);
                return $scope.scrollTimer = null;
            }
        };
        $scope.goMessage = function(message) {
            $rootScope.$broadcast('scroll-to-message', message.cmsg_id);
        };
        
        $scope.unstar = function(message) {
            message.star = false;
            chatStorage.star_message(message.cmsg_id, message.star);
            $rootScope.$broadcast('unstar-message', message);
            return;
        };

        // check to
        $scope.isToMine = function(message) {
            if ($api.is_empty(message.content))
                return false;

            mine = false;
            message.content.replace(/\[to:([^\]]*)\]/g, function(item, user_id) {
                if ($session.user_id + '' == user_id)
                    mine = true;
            });

            return mine;
        };

    }
);