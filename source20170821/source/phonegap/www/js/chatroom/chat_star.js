angular.module('app.chat.star', [])

.controller('chatStarCtrl', 
    function($scope, $api, chatStorage, $rootScope, logger, $session, $state, $timeout, $ionicActionSheet, $ionicScrollDelegate, $ionicModal) {
        var viewScroll = $ionicScrollDelegate.$getByHandle('chat_star_scroll');

        $scope.sync = function() {
            chatStorage.star_messages(null, null).then(function(messages) {
                $scope.messages = messages;
                $scope.initPreviewLink();
            });
        };
        $scope.prev = function() {
            var prev_id;
            if ($scope.messages) {
                prev_id = $scope.messages[0].cmsg_id;
                chatStorage.star_messages(prev_id, null).then(function(messages) {
                    var i, j, ref;
                    if (messages.length > 0) {
                        for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                            $scope.messages.splice(i, 0, messages[i]);
                        }
                        /*
                        if ($scope.messages.length > MAX_MSG_LENGTH) {
                            $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length - MAX_MSG_LENGTH);
                        }
                        */
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
                chatStorage.star_messages(null, next_id).then(function(messages) {
                    var i, j, ref;
                    if (messages.length > 0) {
                        for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                            $scope.messages.push(messages[i]);
                        }
                        /*
                        if ($scope.messages.length > MAX_MSG_LENGTH) {
                            $scope.messages.splice(0, $scope.messages.length - MAX_MSG_LENGTH);
                        }
                        */
                        $scope.startScrollTimer(next_id, "next");
                        $scope.initPreviewLink();
                        return messages;
                    } else {
                        return [];
                    }
                });
            }
        };
        $scope.sync();

        $ionicModal.fromTemplateUrl('templates/chatroom/preview_image.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalPreviewImage = modal;
        });

        $scope.initPreviewLink = function() {
            return $timeout(function() {
                $('.preview-image').off('click').on('click', function() {
                    url = $(this).attr('preview-image');
                    org_url = url;
                    width = $(this).attr('w');
                    height = $(this).attr('h');
                    if (url) {
                        len = url.length;
                        if (url.substring(len-3) != 'gif')
                            url = url + '/1000';
                    }

                    if (width > 0 && height > 0) {
                        if (width > 800) {
                            height = parseInt(height * 800 / width, 10);
                            width = 800;
                        }
                        style = "height: " + height + "px; width: " + width + "px;";
                    }
                    else 
                        style = "min-height: 200px; max-width:800px;";
                        
                    $scope.modalPreviewImage.show();
                    $('#preview_view #board img').remove();
                    $('#preview_download button').remove();
                    $('#preview_view #board').append("<img src='" + url + "' " + style + ">");
                    $('#preview_view').css('height', window.screen.height - 44); // header height: 44px
                    // Add Button and bind to download_image
                    $('#preview_download').append("<button class='button'>ダウンロード</button>");
                    $('#preview_download button').on('click', function(){
                        download_image(org_url);
                    });
                });
            }, 2000);
        };

        $scope.closePreview = function() {
            $scope.modalPreviewImage.hide();
        };
        $scope.startScrollTimer = function(cmsg_id, type) {
            if ($scope.scrollTimer !== null) {
                $scope.stopScrollTimer();
            }
            return $scope.scrollTimer = $timeout(function() {
                var elem = angular.element(document.querySelector('#chat_star_' + cmsg_id));
                var view = angular.element(document.querySelector('#chat_star_view'));
                var viewHeight = view[0].offsetHeight;

                if (elem[0] == null) {
                    return null;
                }

                var rect = elem[0].getBoundingClientRect();                
                var scrollTop = 0;
                if(type == "prev")
                {
                    var elemTop = rect.top;
                    var firstElemId = $scope.messages[0].cmsg_id;
                    var firstElem = angular.element(document.querySelector('#chat_star_' + firstElemId));
                    var firstElemTop = firstElem[0].getBoundingClientRect().top;

                    scrollTop = elemTop - firstElemTop + 2;
                }
                else if(type == "next")
                {
                    var elemTop = rect.top;
                    var firstElemId = $scope.messages[0].cmsg_id;
                    var firstElem = angular.element(document.querySelector('#chat_star_' + firstElemId));
                    var firstElemTop = firstElem[0].getBoundingClientRect().top;

                    scrollTop = elemTop - firstElemTop - view[0].offsetHeight + rect.height - 2;
                }

                viewScroll.scrollTo(rect.left, scrollTop);
                $scope.stopScrollTimer();
            });
        };
        $scope.stopScrollTimer = function() {
            if ($scope.scrollTimer !== null) {
                $timeout.cancel($scope.scrollTimer);
                return $scope.scrollTimer = null;
            }
        };

        $scope.onScrollComplete = function() {
            var elem = angular.element(document.querySelector('#chat_star_view'));
            var scrollTop = viewScroll.getScrollPosition().top;
            var scrollHeight = elem[0].scrollHeight;
            var viewHeight = elem[0].offsetHeight;

            console.log("scrollHeight:" + scrollHeight
                         + " scrollTop:" + scrollTop 
                         + " viewHeight:" + viewHeight);

            if(scrollTop <= 1)
                $scope.prev();
            else if(scrollTop + viewHeight <= scrollHeight+1 && scrollTop + viewHeight >= scrollHeight-1)
                $scope.next();
        };

        $scope.goMessage = function(message) {
            $rootScope.$broadcast('scroll-to-message', message.cmsg_id);
        };
        
        $scope.unstar = function(message) {
            message.star = false;
            chatStorage.star_message(message.cmsg_id, message.star);
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

        $scope.onMessageHold = function(e, itemIndex, message) {
            var buttons = [{
                    text: '<i class="ion-share icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">メッセージへ移動</i>'
                }, {
                    text: '<i class="fa fa-star-o icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">スターを外す</i>'
                }];

            $ionicActionSheet.show({
                buttons: buttons,
                buttonClicked: function(index) {
                    switch (index) {
                        case 0: // go
                            $scope.goMessage(message);
                            break;
                        case 1: // unstar
                            $scope.unstar(message);
                            break;
                    }

                    return true;
                }
            });
        };    
        $scope.goMessage = function(message) {
            $state.transitionTo("tab.star.chatroom", {mission_id: message.mission_id, chat_id: message.cmsg_id});
        };

        $scope.$on('$destroy', function() {
            $scope.modalPreviewImage.remove();
        });

    }
);