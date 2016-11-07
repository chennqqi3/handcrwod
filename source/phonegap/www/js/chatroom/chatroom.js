var read_timer = 500;

angular.module('app.chatroom', [])

.controller('chatroomCtrl', 
    function($scope, $rootScope, $state, $stateParams, 
        $ionicActionSheet, $ionicModal, $session,
        missionStorage, chatStorage, taskStorage, homeStorage, $chat, $ionicPopover, chatizeService,
        $ionicPopup, $ionicScrollDelegate, $timeout, $interval, $api, logger, $dateutil, CONFIG, $compile) {
        $rootScope.nav_id = "chatroom_" + $stateParams.mission_id;
        $scope.isAndroid = ionic.Platform.isAndroid(); 

        $scope.last_cid = null;
        $scope.search_string = null;
        $scope.show_more = false;
        $scope.all_avartar = CONFIG.AVARTAR_URL + "all.jpg"

        var messageCheckTimer;

        var viewScroll = $ionicScrollDelegate.$getByHandle('userMessageScroll');
        var footerBar; // gets set in $ionicView.enter
        var scroller;
        var txtInput; // ^^^
        var keyboardHeight = 0, footerHeight = 0;

        var MAX_MSG_LENGTH = 1000;

        $rootScope.taskMode = 2;

        if ($rootScope.cmsg_sn == undefined)
            $rootScope.cmsg_sn = -1;

        $scope.set_last_msg = function() {
            try {
                msg = localStorage.getItem('r' + $scope.mission_id + '_' + $session.user_id);
            }
            catch (err) {
                msg = '';
            }
                
            if (msg == undefined || msg == 'undefined')
                msg = '';

            $scope.cmsg.content = msg;
        }
        $scope.init_cmsg = function() {
            $scope.cmsg = {
                cmsg_id: $rootScope.cmsg_sn,
                user_id: $session.user_id,
                user_name: $session.user_name,
                content: '',
                read_class: 'read'
            };

            $rootScope.cmsg_sn -= 1;
        };

        $scope.clear_cmsg = function(clear_storage) {
            $scope.cmsg = {
                cmsg_id: $rootScope.cmsg_sn,
                user_id: $session.user_id,
                user_name: $session.user_name,
                content: ''
            }

            $rootScope.cmsg_sn -= 1;

            if (clear_storage == true)
                $scope.save_in_storage();
            return;
        };

        $scope.save_in_storage = function() {
            try {
                key = 'r' + $scope.mission_id + '_' + $session.user_id;
                if ($scope.cmsg == null || $scope.cmsg == undefined || $scope.cmsg.content == '')
                    localStorage.removeItem(key);
                else
                    localStorage.setItem(key, $scope.cmsg.content);
            }
            catch (err) {

            }
            
            return;
        };

        $scope.clear_cmsg();

        $scope.get_message = function(cmsg_id) {
            var i, len, msg, ref;
            if ($scope.messages) {
                ref = $scope.messages;
                for (i = 0, len = ref.length; i < len; i++) {
                    msg = ref[i];
                    if (msg.cmsg_id === cmsg_id) {
                        return msg;
                    }
                }
            }
            return null;
        };

        $scope.load_messages = function(messages) {
            $timeout(function() {
                $('#loader').hide();
                $rootScope.$broadcast('elastic:adjust'); 
            })
            var length;
            $scope.messages = messages;
            // build html
            messages_html = chatStorage.messages_to_html(messages, $scope.chat_id);

            $('#messages').html(messages_html);

            $scope.scrollToBottom(false);
            length = $scope.messages.length;
            if (length > 0) {
                $scope.last_cid = $scope.messages[length - 1].cmsg_id;
            } else {
                $scope.last_cid = null;
            }

            if(!isNaN($scope.chat_id) && !$api.is_empty($scope.chat_id))
            {
                $timeout(function() {
                    $scope.scrollToMessage($scope.chat_id);
                    $scope.chat_id = null;
                });                                        
            }

            $scope.initEventHandler();  
        }

        $scope.$on('receive_messages', function(event, cmsg) {
            messages = cmsg.messages;
            var i, j, ref;
            $scope.chat_id = parseInt($stateParams.chat_id, 10);
            var length;
            if (!$api.is_empty(cmsg.prev_id)) {
                length = $scope.messages.length;
                if (messages.length > 0) {
                    $scope.messages[0].date_label = $dateutil.ellipsis_time_str($scope.messages[0].date, messages[0].date);
                    for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {                            
                        $scope.messages.splice(i, 0, messages[i]);
                    }
                    /*
                    if ($scope.messages.length > MAX_MSG_LENGTH) {
                        $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length - MAX_MSG_LENGTH);
                    }
                    */
                    // build html
                    messages_html = chatStorage.messages_to_html(messages, $scope.chat_id);
                    $('#messages').prepend(messages_html);

                    $scope.startScrollTimer(cmsg.prev_id, "prev");

                    $scope.initEventHandler();
                }
            }
            else if (!$api.is_empty(cmsg.next_id)) {
                length = $scope.messages.length;
                if (messages.length > 0) {
                    messages[0].date_label = $dateutil.ellipsis_time_str(messages[0].date, $scope.messages[length-1].date);

                    for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                        $scope.messages.push(messages[i]);
                    }
                    /*
                    if ($scope.messages.length > MAX_MSG_LENGTH) {
                        $scope.messages.splice(0, $scope.messages.length - MAX_MSG_LENGTH);
                    }
                    */
                    // build html
                    messages_html = chatStorage.messages_to_html(messages, $scope.chat_id);
                    $('#messages').append(messages_html);
                    $scope.startScrollTimer(cmsg.next_id, "next");

                    $scope.initEventHandler();
                }
            }
            else {
                chatStorage.cache_messages($scope.mission_id, messages);
                $scope.load_messages(messages);
            }
        });

        $scope.react = function(cmsg_id, emoticon_id) {
            $chat.react(cmsg_id, $scope.mission_id, emoticon_id);
        };

        $scope.$on('react_message', function(event, cmsg) {
            var i, len, m, ref;
            if (cmsg.mission_id === $scope.mission_id) {
                console.log("receive cmsg_id:" + cmsg.cmsg_id);
                ref = $scope.messages;
                for (i = 0, len = ref.length; i < len; i++) {
                    m = ref[i];
                    if (m.cmsg_id === cmsg.cmsg_id) {
                        m.reacts = cmsg.reacts;
                        $scope.render_message(m);
                        break;
                    }
                }
                //$scope.initEventHandler();
            }
        });

        $scope.$on('$ionicView.loaded', function() {
            angular.element(document.querySelector('body')).on('click', function(e) {
                var elem = angular.element(document.querySelector('#input_bar .btn-emoticon'));
                if (elem[0] != e.target && elem[0] != e.target.paerntNode) {
                    angular.element(document.querySelector('#emoticon_gallery')).css('visibility', 'hidden');
                    elem.data('isShowing', "false");
                }

                var elem = angular.element(document.querySelector('#input_bar .btn-to'));
                var elem_to_users = angular.element(document.querySelector('#to_users'));
                if (elem[0] != e.target && elem[0] != e.target.paerntNode && 
                    elem_to_users[0] != e.target.parentNode && elem_to_users[0] != e.target.parentNode.parentNode) {
                    elem_to_users.css('visibility', 'hidden');
                    elem.data('isShowing', "false");
                }
            });
        });

        $scope.showEmoticons = function(e) {
            var btn_rect, elem, gheight, gwidth, isShowing, pos;
            if (e)
                e.preventDefault();
            elem = angular.element(document.querySelector('#input_bar .btn-emoticon'));
            var emoticon_gallery = angular.element(document.querySelector('#emoticon_gallery'));            
            isShowing = elem.data('isShowing');
            elem.removeData('isShowing');
            if (isShowing !== 'true') {
                elem.data('isShowing', "true");
                emoticon_gallery.css('visibility', 'visible');
            } else {
                elem.data('isShowing', "false");
                emoticon_gallery.css('visibility', 'hidden');
            }
        };

        $scope.add_emoticon = function(emoticon_id, emo_text) {
            if ($scope.cmsg_id) {
                $scope.react($scope.cmsg_id, emoticon_id);
                $scope.cmsg_id = undefined;
            }
            else {
                var start, str, strPrefix, strSuffix;
                start = angular.element(document.querySelector('.item-input-wrapper textarea')).prop("selectionStart");
                str = "";
                if ($api.is_empty($scope.cmsg.content)) {
                    str = emo_text;
                    start = str.length;
                } else {
                    strPrefix = $scope.cmsg.content.substring(0, start);
                    strSuffix = $scope.cmsg.content.substring(start);
                    start += emo_text.length;
                    str = strPrefix + emo_text + strSuffix;
                }
                $scope.cmsg.content = str;
                $timeout(function() {
                    chat_ta = document.getElementById('chat_ta');
                    chat_ta.focus();
                    chat_ta.setSelectionRange(start, start);
                });
            }
            angular.element(document.querySelector('#input_bar .btn-emoticon')).data('isShowing', "false");
            angular.element(document.querySelector('#emoticon_gallery')).css('visibility', 'hidden');
        };

        $scope.showMore = function(show) {
            $scope.show_more = show;
        };

        $scope.showTo = function() {
            var btn_rect, ele, gheight, gwidth, isShowing, pos;
            ele = angular.element(document.querySelector('#input_bar .btn-to'));
            var to_users = angular.element(document.querySelector('#to_users')); 
            isShowing = ele.data('isShowing');
            ele.removeData('isShowing');
            if (isShowing !== 'true') {
                ele.data('isShowing', "true");
                to_users.css('visibility', 'visible');
            } else {
                ele.data('isShowing', "false");
                to_users.css('visibility', 'hidden');
            }
        };

        $scope.to_message = function(member) {
            txta = angular.element(document.querySelector('.item-input-wrapper textarea'));
            start = txta.prop("selectionStart");
            str = "";
            if (member == undefined)
                to_text = "[to:all]全員\n";
            else
                to_text = "[to:" + member.user_id + "]" + member.user_name + "さん\n";
            if ($api.is_empty($scope.cmsg.content)) {
                str = to_text;
                start = str.length;
            }
            else {
                strPrefix = $scope.cmsg.content.substring(0, start);
                strSuffix = $scope.cmsg.content.substring(start);
                start += to_text.length;
                str = strPrefix + to_text + strSuffix;
            }
            $scope.cmsg.content = str;
            angular.element(document.querySelector('#input_bar .btn-to')).data('isShowing', "false");
            angular.element(document.querySelector('#to_users')).css('visibility', 'hidden');
            $timeout(function() {
                chat_ta = document.getElementById('chat_ta');
                chat_ta.focus();
                chat_ta.setSelectionRange(start, start);
            });

            $scope.show_more = false;
            return;
        }

        $scope.onUploadFiles = function(files) {
            $ionicPopup.confirm({
                title: 'ファイル送信',
                template: 'このファイル（写真や動画）を送信します。よろしいですか？',
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            if($api.is_empty($scope.files) || $scope.files.length == 0)
                                $scope.files = files;
                            else
                                $scope.files = $scope.files.concat(files);

                            $timeout(function() {
                                $rootScope.$broadcast('elastic:adjust');
                            });
                            
                            return angular.forEach(files, function(file) {
                                file.retry = 0;
                                $scope.uploadOneFile(file);
                            });     
                        }
                    }
                ]
            });

            $scope.show_more = false;
        };

        $scope.uploadOneFile = function(file) {
            var size, upload;
            file.progress = 0;
            size = Math.round(file.size * 100 / (1024 * 1024)) / 100;
            file.fileSize = size;
            file.retry ++;

            if (file.fileTransferMode) {
                file.onSuccess = function(data) {
                    var i, str;
                    i = $scope.files.indexOf(file);
                    $scope.files.splice(i, 1);
                    if (data.err_code === 0) {
                        str = "[file id=" + data.mission_attach_id + " url='" + data.mission_attach_url + "']" + file.name + "[/file]";
                        $chat.send(null, $rootScope.cur_home.home_id, $scope.mission_id, str, null, 1);
                    } else {
                        console.log(data.err_msg);
                        logger.logError(data.err_msg);
                    }

                    $scope.$apply();
                    $('#btn_upload input').val('');
                };

                file.onError = function(error) {
                    $('#btn_upload input').val('');
                    if (file.retry <= 4) {
                        if (file.canceled != true) {
                            $timeout(function() {
                                $scope.uploadOneFile(file);
                            }, 1000);
                        }
                    }
                    else {
                        file.retry = 0;
                        $ionicPopup.confirm({
                            title: 'ファイル送信失敗',
                            template: 'ファイル送信が失敗しました。再度送信しますか？',
                            buttons: [
                                { text: 'キャンセル' },
                                {
                                    text: '<b>OK</b>',
                                    type: 'button-positive',
                                    onTap: function(e) {
                                        $scope.uploadOneFile(file);       
                                    }
                                }
                            ]
                        });
                    }
                };

                file.onProgress = function(evt) {
                    file.progress = parseInt(100.0 * evt.loaded / evt.total);
                    console.log("progress " + file.progress + "%");
                    $scope.$apply();
                };

                file.upload = chatStorage.upload_file(true, $scope.mission_id, file);
            }
            else {
                upload = chatStorage.upload_file(false, $scope.mission_id, file).progress(function(evt) {
                    return file.progress = parseInt(100.0 * evt.loaded / evt.total);
                }).success(function(data, status, headers, config) {
                    var i, str;
                    i = $scope.files.indexOf(file);
                    $scope.files.splice(i, 1);
                    $('#btn_upload input').val('');
                    if (data.err_code === 0) {
                        str = "[file id=" + data.mission_attach_id + " url='" + data.mission_attach_url + "']" + file.name + "[/file]";
                        $chat.send(null, $rootScope.cur_home.home_id, $scope.mission_id, str, null, 1);
                    } else {
                        console.log(data.err_msg);
                        logger.logError(data.err_msg);
                    }
                }).error(function() {
                    $('#btn_upload input').val('');
                    if (file.retry <= 4) {
                        if (file.canceled != true) {
                            $timeout(function() {
                                $scope.uploadOneFile(file);
                            }, 1000);
                        }
                    }
                    else {
                        file.retry = 0;
                        $ionicPopup.confirm({
                            title: 'ファイル送信失敗',
                            template: 'ファイル送信が失敗しました。再度送信しますか？',
                            buttons: [
                                { text: 'キャンセル' },
                                {
                                    text: '<b>OK</b>',
                                    type: 'button-positive',
                                    onTap: function(e) {
                                        $scope.uploadOneFile(file);       
                                    }
                                }
                            ]
                        });
                    }
                });
                file.upload = upload;
            }
        }

        $scope.onCancelUpload = function(file) {
            var i;
            if (file.upload) {
                chatStorage.cancel_upload_file(file);
                i = $scope.files.indexOf(file);
                $scope.files.splice(i, 1);
            }
        };

        $scope.onCapture = function(video) {
            if (video) {
                navigator.device.capture.captureVideo(function(mediaFiles) {
                    if (mediaFiles.length > 0) {
                        file = mediaFiles[0];
                        file.fileTransferMode = true
                        $scope.onUploadFiles([file]);
                    }
                }, function(message) {

                }, {limit:1});
            }
            else {
                navigator.device.capture.captureImage(function(mediaFiles) {
                    if (mediaFiles.length > 0) {
                        file = mediaFiles[0];
                        file.fileTransferMode = true
                        $scope.onUploadFiles([file]);
                    }
                }, function(message) {
                    
                }, {limit:1});
            }

            $scope.show_more = false;
        };

        $scope.scrollTimer = null;
        $scope.startScrollTimer = function(cmsg_id, type) {
            if ($scope.scrollTimer !== null) {
                $scope.stopScrollTimer();
            }
            return $scope.scrollTimer = $timeout(function() {
                var elem = angular.element(document.querySelector('#chat_' + cmsg_id));
                var view = angular.element(document.querySelector('#chat_view'));
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
                    var firstElem = angular.element(document.querySelector('#chat_' + firstElemId));
                    var firstElemTop = firstElem[0].getBoundingClientRect().top;

                    scrollTop = elemTop - firstElemTop + 2;
                }
                else if(type == "next")
                {
                    var elemTop = rect.top;
                    var firstElemId = $scope.messages[0].cmsg_id;
                    var firstElem = angular.element(document.querySelector('#chat_' + firstElemId));
                    var firstElemTop = firstElem[0].getBoundingClientRect().top;

                    scrollTop = elemTop - firstElemTop - view[0].offsetHeight + rect.height - 2;
                }
                else if(type == "cur")
                {
                    var elemTop = rect.top;
                    var firstElemId = $scope.messages[0].cmsg_id;
                    var firstElem = angular.element(document.querySelector('#chat_' + firstElemId));
                    var firstElemTop = firstElem[0].getBoundingClientRect().top;

                    elem.find();
                    scrollTop = elemTop - firstElemTop - (view[0].offsetHeight / 2) + rect.height;
                }

                viewScroll.scrollTo(rect.left, scrollTop);
                $('#chat_view').removeClass('transparent');
                $scope.stopScrollTimer();
            });

            console.log("startScrollTimer() " + cmsg_id);            
        };
        $scope.stopScrollTimer = function() {
            if ($scope.scrollTimer !== null) {
                $timeout.cancel($scope.scrollTimer);
                return $scope.scrollTimer = null;
            }
        };

        $scope.onScrollComplete = function() {
            var elem = angular.element(document.querySelector('#chat_view'));
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

        $scope.prev = function() {           
            var prev_id;
            if ($scope.messages) {
                prev_id = $scope.messages[0].cmsg_id;
                console.log("prev() " + prev_id);

                return chatStorage.messages($scope.mission_id, prev_id)
                    .then(function(messages) {
                        var i, j, ref;
                        if (messages.length > 0) {
                            $scope.messages[0].date_label = $dateutil.ellipsis_time_str($scope.messages[0].date, messages[0].date);
                            for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {                            
                                $scope.messages.splice(i, 0, messages[i]);
                            }
                            /*
                            if ($scope.messages.length > MAX_MSG_LENGTH) {
                                $scope.messages.splice(MAX_MSG_LENGTH, $scope.messages.length - MAX_MSG_LENGTH);
                            }
                            */

                            // build html
                            messages_html = chatStorage.messages_to_html(messages, $scope.chat_id);
                            $('#messages').prepend(messages_html);

                            $scope.startScrollTimer(prev_id, "prev");

                            $scope.initEventHandler();

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

                if (next_id < 0)
                    return null;

                console.log("next() " + next_id);
                
                return chatStorage.messages($scope.mission_id, null, next_id)
                    .then(function(messages) {
                        var i, j, ref, added_message = [];
                        if (messages.length > 0) {
                            messages[0].date_label = $dateutil.ellipsis_time_str(messages[0].date, $scope.messages[length-1].date);
                            last_cmsg_id = $scope.messages[$scope.messages.length - 1].cmsg_id;

                            if (last_cmsg_id < 0)
                                return [];

                            for (i = j = 0, ref = messages.length - 1; 0 <= ref ? j <= ref : j >= ref; i = 0 <= ref ? ++j : --j) {
                                var cmsg_id = messages[i].cmsg_id;
                                if ($('#chat_' + cmsg_id).length == 0) {
                                    console.log("add message last:" + last_cmsg_id + " cmsg:" + messages[i].cmsg_id);
                                    $scope.messages.push(messages[i]);
                                    added_message.push(messages[i])
                                }
                            }
                            /*
                            if ($scope.messages.length > MAX_MSG_LENGTH) {
                                $scope.messages.splice(0, $scope.messages.length - MAX_MSG_LENGTH);
                            }
                            */

                            // build html
                            messages_html = chatStorage.messages_to_html(added_message, $scope.chat_id);
                            $('#messages').append(messages_html);

                            $scope.startScrollTimer(next_id, "next");

                            $scope.initEventHandler();

                            return added_message;
                        } else {
                            return [];
                        }
                    });
            }
        };
        $scope.$on("synced-server", function() {
            $scope.sync();
        });
        $scope.isMessageExist = function(cmsg_id) {
            var exist, j, len, message, ref;
            exist = false;
            ref = $scope.messages;
            for (j = 0, len = ref.length; j < len; j++) {
                message = ref[j];
                if (message.cmsg_id == cmsg_id) {
                    $scope.render_message(message);
                    exist = true;
                    break;
                }
            }
            return exist;
        };

        $scope.scrollToMessage = function(cmsg_id, callback) {
            var f_msg, l_msg, length;
            if ($scope.isMessageExist(cmsg_id)) {
                $scope.startScrollTimer(cmsg_id, "cur");
                if (callback)
                    callback();
                return;
            } else {
                length = $scope.messages.length;
                if (length > 0) {
                    f_msg = $scope.messages[0];
                    l_msg = $scope.messages[length - 1];
                    if (f_msg.cmsg_id > cmsg_id) {
                        $scope.prev().then(function(messages) {
                            if ($scope.isMessageExist(cmsg_id)) {
                                $scope.startScrollTimer(cmsg_id, "cur");
                                if (callback)
                                    callback();
                                return;
                            } else {
                                $scope.stopScrollTimer();
                                return $scope.scrollToMessage(cmsg_id, callback);
                            }
                        });
                    } else if (l_msg.cmsg_id < cmsg_id) {
                        res = $scope.next();
                        if (res != null) {
                            res.then(function(messages) {
                                if ($api.is_empty(messages) || messages.length == 0) {
                                    if (callback)
                                        callback();
                                    return;
                                }

                                if ($scope.isMessageExist(cmsg_id)) {
                                    $scope.startScrollTimer(cmsg_id, "cur");
                                    if (callback)
                                        callback();
                                    return;
                                } else {
                                    return $scope.scrollToMessage(cmsg_id, callback);
                                }
                            });
                        }
                        else {
                            if (callback)
                                callback();
                        }
                    }
                }
            }
        };
        $scope.onSelectSearchMessage = function(action, message) {
            if (action === 1) {
                return $scope.scrollToMessage(message.cmsg_id);
            } else if (action === 2) {
                return $scope.quote(message);
            }
        };
        $scope.search = function() {
            if (!$api.is_empty($scope.search_string)) {
                $dialogs.chatSearch($scope.mission_id, $scope.search_string, $scope.onSelectSearchMessage);
            }
        };
        $scope.exitSearch = function() {
            return $scope.search_string = null;
        };

        $scope.render_message = function(message) {
            console.log("set message temp:" + message.temp_cmsg_id + " id:" + message.cmsg_id + " message:" + message.content);
            if (message.cmsg_id > 0 && message.temp_cmsg_id < 0) {
                $('#chat_' + message.temp_cmsg_id).remove();   
            }

            // build html
            if ($('#chat_' + message.cmsg_id).length > 0) {
                message_html = chatStorage.message_to_html(message, $scope.chat_id, false);
                $('#chat_' + message.cmsg_id).html(message_html);
                $compile($('#chat_' + message.cmsg_id).contents())($scope);
            }
            else {
                message_html = chatStorage.message_to_html(message, $scope.chat_id);
                el = $(message_html);
                $compile(el.contents())($scope);
                $('#messages').append(el);
            }

            $scope.initEventHandler();
        };
        $scope.sendMessage = function() {
            if ($api.is_empty($scope.cmsg.content))
                return;

            length = $scope.messages.length;
            if (length > 1) {
                l_msg = $scope.messages[length-1];
                if ($scope.last_cid != l_msg.cmsg_id) { // scrolled top and bottom messages was cutted
                    $scope.scrollToMessage($scope.last_cid, function() {
                        chatStorage.set_message($scope.mission_id, $scope.messages, $scope.cmsg, $scope.render_message);
                    }); // scroll to last cid
                }
                else {
                    chatStorage.set_message($scope.mission_id, $scope.messages, $scope.cmsg, $scope.render_message);
                    $scope.scrollToBottom();
                }
            }

            $chat.send($scope.cmsg.cmsg_id, $rootScope.cur_home.home_id, $scope.mission_id, $scope.cmsg.content);

            $scope.clear_cmsg(true);
        };
        $scope.$on('receive_message', function(event, cmsg) {
            var l_msg, length;
            if (cmsg.mission_id == $scope.mission_id) {
                chatStorage.set_message($scope.mission_id, $scope.messages, cmsg, $scope.render_message);
                length = $scope.messages.length;
                if (length > 1) {
                    if (cmsg.inserted) {
                        l_msg = $scope.messages[length - 2];
                        if ($scope.last_cid !== l_msg.cmsg_id) {
                            $scope.messages.splice(length - 1, 1);
                            $scope.scrollToMessage(cmsg.cmsg_id);
                            $scope.last_cid = cmsg.cmsg_id;
                        } else {
                            $scope.last_cid = cmsg.cmsg_id;
                            cmsg.date_label = $dateutil.ellipsis_time_str(cmsg.date, l_msg.date);
                            $scope.scrollToBottom();
                        }
                    } else if (cmsg.cmsg_id > $scope.last_cid) {
                        $scope.last_cid = cmsg.cmsg_id;
                    }
                }
                else if (length == 1) {
                    $scope.last_cid = cmsg.cmsg_id;
                }
                
                $scope.$apply();

                $scope.initEventHandler();
                
                $scope.scrollToBottom();
            }
        });

        $ionicModal.fromTemplateUrl('templates/chatroom/preview_image.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modalPreviewImage = modal;
        });

        $scope.initEventHandler = function() {
            $timeout(function() {
                $('.preview-image').off('click').on('click', function() {
                    url = $(this).attr('preview-image');
                    $scope.modalPreviewImage.show();
                    $('#preview_view #board img').remove();
                    $('#preview_view #board').append("<img width='100%' src='" + url + "'>");
                    $('#preview_view').css('height', window.screen.height - 44); // header height: 44px
                });

                $('.message-wrapper').off('hold').on("hold", function(e) {
                    console.log('hold ');
                    cmsg_id = $(this).data('cmsg_id');
                    for (i = 0; i < $scope.messages.length; i ++) {
                        if ($scope.messages[i].cmsg_id == cmsg_id) {
                            $scope.onMessageHold($scope.messages[i], e);
                            break;
                        }
                    } 
                });
            }, 2000);
        }

        $scope.scrollToBottom = function(animate) {
            return $timeout(function() {
                viewScroll.scrollBottom(animate);
                if (!animate)
                    $timeout(function() {
                        $('#chat_view').removeClass('transparent');
                    }, 500);
            }, 1000);
        };

        $scope.closePreview = function() {
            $scope.modalPreviewImage.hide();
        };
            
        $scope.addTask = function(message) {
            $scope.task = {
                mission_id: $scope.mission_id,
                task_name: chatizeService.strip(message.content)
            }

            // An elaborate, custom popup
            var popNewTask = $ionicPopup.show({
                template: '<input type="text" ng-model="task.task_name" placeholder="タスク名を入力してください。">',
                title: 'タスク新規登録',
                scope: $scope,
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            if (!$scope.task.task_name) {
                                e.preventDefault();
                            } else {
                                taskStorage.add($scope.task, function(res) {
                                    if (res.err_code == 0) {
                                        $scope.init();
                                        logger.logSuccess('新しいタスクが作成されました。');
                                    }
                                    else
                                        logger.logError(res.err_msg);
                                });

                                return;
                            }
                        }
                    }
                ]
            });

            popNewTask.then(function() {
                
            });
        }
        $scope.quote = function(message) {
            var start, str, strPrefix, strSuffix, time;
            start = angular.element(document.querySelector('.item-input-wrapper textarea')).prop("selectionStart");
            time = new Date(message.date).getTime() / 1000;
            str = "[引用 id=" + message.user_id + " name='" + message.user_name + "' time=" + time + "]" + message.content + "[/引用 time=" + time + "]";
            if (!$api.is_empty($scope.cmsg.content)) {
                strPrefix = $scope.cmsg.content.substring(0, start);
                strSuffix = $scope.cmsg.content.substring(start);
                start += str.length;
                str = strPrefix + str + strSuffix;
            }
            else
                start = str.length;
            $scope.cmsg.content = str + "\n";
            $timeout(function() {
                chat_ta = document.getElementById('chat_ta');
                chat_ta.focus();
                chat_ta.setSelectionRange(start + 1, start + 1);
            });
        };

        $scope.link = function(message) {
            start = angular.element(document.querySelector('.item-input-wrapper textarea')).prop("selectionStart");
            time = new Date(message.date).getTime() / 1000;
            str = "[link href='" + $scope.mission_id + "/" + message.cmsg_id + "'][/link]";
            if (!$api.is_empty($scope.cmsg.content)) {
                strPrefix = $scope.cmsg.content.substring(0, start);
                strSuffix = $scope.cmsg.content.substring(start);
                start += str.length;
                str = strPrefix + str + strSuffix;
            }
            else
                start = str.length;
            $scope.cmsg.content = str;
            $timeout(function() {
                chat_ta = document.getElementById('chat_ta');
                chat_ta.focus();
                chat_ta.setSelectionRange(start, start);
            });
            return;
        }

        $scope.edit = function(message) {
            $scope.cmsg.editing = false;
            $scope.cmsg.cmsg_id = message.cmsg_id;
            $scope.cmsg.content = message.content;

            message.editing = true;
            $scope.render_message(message);

            $timeout(function() { document.querySelector('#chat_ta').focus();})
        };
        $scope.exitEdit = function() {
            if(!$api.is_empty($scope.cmsg.content))
            {
                $scope.cmsg.editing = false;
                $scope.render_message($scope.cmsg);
                $scope.clear_cmsg(true);
            }

            return;
        };
        $scope.remove = function(message) {
            var confirmPopup = $ionicPopup.confirm({
                title: 'メッセージ削除',
                template: 'このメッセージを削除してもよろしいでしょうか？',
                buttons: [
                    { text: 'キャンセル' },
                    {
                        text: '<b>OK</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            $chat.remove(message.cmsg_id, $scope.mission_id);
                        }
                    }
                ]
            });
            return 
        };
        $scope.$on('remove_message', function(event, cmsg) {
            var length;
            if (cmsg.mission_id === $scope.mission_id) {
                is_to_mine = chatStorage.is_to_mine(cmsg);
                chatStorage.remove_message($scope.messages, cmsg);
                if (cmsg.unread) {
                    delta = -1;
                    delta_to = 0;
                    if (is_to_mine)
                        delta_to = -1;
                    $rootScope.cur_mission.unreads += delta;
                    $rootScope.cur_mission.to_unreads += delta;
                    $rootScope.cur_home.unreads += delta;
                    $rootScope.cur_home.to_unreads += delta_to;
                    chatStorage.remove_unread(cmsg);
                    chatStorage.refresh_unreads_title();
                }
                if (cmsg.cmsg_id === $scope.last_cid) {
                    length = $scope.messages.length;
                    if (length > 0) {
                        $scope.last_cid = $scope.messages[length - 1].cmsg_id;
                    } else {
                        $scope.last_cid = null;
                    }
                }

                $('#chat_' + cmsg.cmsg_id).remove();   
                return $scope.$apply();
            }
        });

        $scope.star = function(message) {
            if (message.star)
                message.star = false;
            else
                message.star = true;

            $scope.render_message(message);

            chatStorage.star_message(message.cmsg_id, message.star);
        };

        $rootScope.$on('unstar-message', function(evt, message) {
            len = $scope.messages.length;
            for (i = 0; i < len; i ++) {
                if ($scope.messages[i].cmsg_id == message.cmsg_id)
                    $scope.messages[i].star = false;
            }
        });

        $scope.unread = function(message) {
            chatStorage.unread_messages($scope.mission_id, [message.cmsg_id]).then(function(data) {
                var mission;
                if (data.err_code === 0) {
                    message.unread = true;
                    message.set_unread = true;
                    $scope.set_unread = true;
                    message.read_class = "unread";
                    if ($('#chat_' + message.cmsg_id).length > 0) {
                        $('#chat_' + message.cmsg_id + ' i.unread-mark').addClass(message.read_class).removeClass("read");
                    }
                    delta = 1;
                    delta_to = 0;
                    message.to_flag = chatStorage.is_to_mine(message);
                    if (message.to_flag)
                        delta_to = 1;

                    $rootScope.cur_mission.unreads += delta;
                    $rootScope.cur_mission.to_unreads += delta_to;
                    $rootScope.cur_home.unreads += delta;
                    $rootScope.cur_home.unreads += delta_to;

                    $rootScope.$broadcast('unread-message', $rootScope.cur_mission);
                    missionStorage.set_mission($rootScope.cur_mission);
                    return chatStorage.refresh_unreads_title();
                } else {
                    return logger.logError(data.err_msg);
                }
            });
        };        

        $scope.copy = function(message) {
            var start, str, strPrefix, strSuffix, time;
            start = angular.element(document.querySelector('.item-input-wrapper textarea')).prop("selectionStart");
            time = new Date(message.date).getTime() / 1000;
            str = message.content;
            if (!$api.is_empty($scope.cmsg.content)) {
                strPrefix = $scope.cmsg.content.substring(0, start);
                strSuffix = $scope.cmsg.content.substring(start);
                start += str.length;
                str = strPrefix + str + strSuffix;
            }
            else
                start = str.length;
            $scope.cmsg.content = str;
            $timeout(function() {
                chat_ta = document.getElementById('chat_ta');
                chat_ta.focus();
                chat_ta.setSelectionRange(start + 1, start + 1);
            });
        };

        $scope.reaction = function(message, e) {
            $scope.cmsg_id = message.cmsg_id;
            $timeout(function() {
                $scope.showEmoticons();
            }, 500);
        };

        angular.element(document.querySelector('#chat_view')).on('scroll', function() {
            return $scope.startReadTimer();
        });

        $scope.readTimer = null;
        $scope.startReadTimer = function() {
            if ($scope.readTimer !== null) {
                $scope.stopReadTimer();
            }
            return $scope.readTimer = $timeout(function() {
                return $scope.readInScroll();
            }, read_timer);
        };
        $scope.stopReadTimer = function() {
            if ($scope.readTimer !== null) {
                $timeout.cancel($scope.readTimer);
                return $scope.readTimer = null;
            }
        };
        $scope.readInScroll = function() {
            var j, len, message, messageTop, messages, parentRect, readIds;
            messages = $scope.messages;
            var parent = angular.element(document.querySelector('#chat_view'))[0];
            if(parent)
                parentRect = parent.getBoundingClientRect();
            readIds = [];

            if(parentRect && messages != null)
            {
                for (j = 0, len = messages.length; j < len; j++) {
                    message = messages[j];
                    var elem = angular.element(document.querySelector('#chat_' + message.cmsg_id))[0];
                    var elemRect = null;
                    if(elem)
                        elemRect = elem.getBoundingClientRect();

                    if(elemRect)
                    {
                        messageTop = elemRect.top;                

                        if (messageTop !== void 0) {
                            if (messageTop >= parentRect.top && messageTop < parentRect.bottom && message.unread && !message.set_unread) {
                                readIds.push(message.cmsg_id);
                            }
                        }
                    }
                }
            }
            if (readIds.length > 0) {
                return chatStorage.read_messages($scope.mission_id, readIds).then(function(data) {
                    var k, len1, results;
                    if (data.err_code === 0) {
                        results = [];
                        for (k = 0, len1 = messages.length; k < len1; k++) {
                            message = messages[k];
                            if (readIds.indexOf(message.cmsg_id) !== -1) {
                                message.unread = false;
                                message.read_class = "unread read";
                                if ($('#chat_' + message.cmsg_id).length > 0) {
                                    $('#chat_' + message.cmsg_id + ' .unread-mark').addClass(message.read_class);
                                }
                                delta = -1;
                                delta_to = 0;
                                message.to_flag = chatStorage.is_to_mine(message);
                                if (message.to_flag)
                                    delta_to = -1;
                                $rootScope.cur_mission.unreads += delta;
                                $rootScope.cur_mission.to_unreads += delta_to;
                                $rootScope.cur_home.unreads += delta;
                                $rootScope.cur_home.to_unreads += delta_to;
                            }
                        }

                        missionStorage.set_mission($rootScope.cur_mission);
                        chatStorage.refresh_unreads_title();
                    } else {
                        return logger.logError(data.err_msg);
                    }
                });
            }
        };
        $scope.memberMission = function() {
            missionStorage.get($scope.mission_id, function(res) {
                if (res.err_code === 0) {
                    $scope.mission = res.mission;
                } else {
                    logger.logError(res.err_msg);
                }
            });
        };        

        $scope.$on('$ionicView.enter', function() {
            $timeout(function() {
                footerBar = document.body.querySelector('#messageView .bar-footer');
                scroller = document.body.querySelector('#messageView .scroll-content');
                txtInput = angular.element(footerBar.querySelector('textarea'));
            }, 0);

            messageCheckTimer = $interval(function() {
                // here you could check for new messages if your app doesn't use push notifications or user disabled them
            }, 20000);

            $scope.startReadTimer();
        });

        $scope.$on('$ionicView.leave', function() {
            $scope.stopReadTimer();
        });

        $scope.$on('$ionicView.beforeLeave', function() {
        });

        // this keeps the keyboard open on a device only after sending a message, it is non obtrusive
        function keepKeyboardOpen() {
            console.log('keepKeyboardOpen');
            txtInput.one('blur', function() {
                console.log('textarea blur, focus back on it');
                txtInput[0].focus();
            });
        }

        $scope.onMessageHold = function(message, e) {
            var buttons = [
                { text: '<i class="fa fa-check-square-o icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">タスク新規登録</i>' }, 
                { text: '<i class="icon-link icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">メッセージリンク</i>' }, 
                { text: '<i class="ion-quote icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">メッセージ引用</i>' }, 
                { text: '<i class="ion-edit icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">メッセージ編集</i>' }, 
                { text: '<i class="ion-trash-a icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">メッセージ削除</i>' }, 
                { text: '<i class="fa ' + (message.star ? 'fa-star' : 'fa-star-o') + ' icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">スター付き</i>' },
                { text: '<i class="ion-ios-copy-outline icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">メッセージコピー</i>' },
                { text: '<i class="icon-emoticon-smile icon-button icon-action" ></i><span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">リアクション</i>' },
                { text: '<span class="tab-action">&nbsp;&nbsp;&nbsp;</span><i class="text-action">未読にする</i>' }
            ];
            var indices = [0, 1, 2, 3, 4, 5, 6, 7, 8];

            if (message.user_id !== $session.user_id) {
                buttons.splice(3, 2);
                indices.splice(3, 2);
            }

            if (!$rootScope.canEditTask()) {
                buttons.splice(0, 1);
                indices.splice(0, 1);
            }

            $ionicActionSheet.show({
                buttons: buttons,
                buttonClicked: function(index) {
                    switch (indices[index]) {
                        case 0: // add task
                            $scope.addTask(message);
                            break;
                        case 1: // link
                            $scope.link(message);
                            break;
                        case 2: // quote
                            $scope.quote(message);
                            break;
                        case 3: // edit
                            $scope.edit(message);
                            break;
                        case 4: // Delete
                            $scope.remove(message);
                            break;
                        case 5: // star
                            $scope.star(message);
                            break;
                        case 6: // copy
                            $scope.copy(message);
                            break;
                        case 7: // reaction
                            $scope.reaction(message, e);
                            break;
                        case 8: // unread
                            $scope.unread(message);
                    }

                    return true;
                }
            });
        };

        // menu
        $ionicPopover.fromTemplateUrl('chatroom-menu.html', {
            scope: $scope
        }).then(function(popover) {
            $scope.otherMenu = popover;
        });

        $scope.showOthers = function($event) {
            $scope.otherMenu.show($event);
        }

        $scope.hideOthers = function($event) {
            $scope.otherMenu.hide($event);   
        }

        $scope.$on('$destroy', function() {
            $scope.otherMenu.remove();
            $scope.modalPreviewImage.remove();

            angular.forEach($rootScope.missions, function(mission) {
                if (mission.mission_id == $scope.mission_id && mission.unreads > 0 && !$scope.set_unread) {
                    chatStorage.read_messages($scope.mission_id)
                        .then(function(data) {
                            if (data.err_code == 0) {
                                $timeout(function() {
                                    mission.unreads = 0;
                                    mission.to_unreads = 0;
                                    $rootScope.unreads[$scope.mission_id] = [];
                                    chatStorage.refresh_unreads_title();
                                }, 1000);
                            }
                            else
                                logger.logError(data.err_msg);
                        });
                }
                return
            });
        });

        // check to
        $scope.isToMine = function(message) {
            if ($api.is_empty(message))
                return false;

            message.content += "";
            if ($api.is_empty(message.content))
                return false;

            mine = false;
            message.content.replace(/\[to:([^\]]*)\]/g, function(item, user_id) {
                if ($session.user_id + '' == user_id)
                    mine = true;
            });

            return mine;
        }

        // show user profile
        $scope.showUserProfile = function(url) {
            $scope.modalPreviewImage.show();
            $('#preview_view #board img').remove();
            $('#preview_view #board').append("<img width='100%' src='" + url + "'>");
            $('#preview_view').css('height', window.screen.height - 44); // header height: 44px
            return;
        }

        $scope.sync = function() {
            $scope.session = $session;
            $scope.mission_id = parseInt($stateParams.mission_id, 10);
            $scope.chat_id = parseInt($stateParams.chat_id, 10);

            /*
            if (isNaN($scope.chat_id))
                $scope.load_messages(chatStorage.cache_messages($scope.mission_id));
            */

            mission = missionStorage.get_mission($scope.mission_id);
            if ($session.user_id !== null) {
                missionStorage.set_cur_mission(mission);

                if ($api.is_empty(mission) || ((mission.private_flag==0 || mission.private_flag==1) && $api.is_empty(mission.members)))
                {
                    missionStorage.get($scope.mission_id, function(res) {
                        console.log("get mission detail");
                        var m;
                        if (res.err_code === 0) {
                            if (res.mission.private_flag != 2 && $rootScope.cur_home.home_id != res.mission.home_id) {
                                home = homeStorage.get_home(res.mission.home_id);
                                if (home == null) {
                                    $state.go('tab.chats');
                                    return;
                                }
                                console.log("new set_cur_home");
                                homeStorage.set_cur_home(home);
                            }
                            else if (res.mission.private_flag == 2 && $rootScope.cur_home)
                                res.mission.home_id = $rootScope.cur_home.home_id;
                            
                            missionStorage.set_cur_mission(res.mission);
                        } else {
                            logger.logError(res.err_msg);
                            $state.go('tab.chats');
                        }
                    });
                }

                $scope.init_cmsg();
                $chat.messages($scope.mission_id);
                /*
                chatStorage.messages($scope.mission_id).then(function(messages) {
                    $timeout(function() {
                        $('#loader').hide();
                    }, 1000)
                    var length;
                    $scope.messages = messages;
                    $scope.scrollToBottom(false);
                    length = $scope.messages.length;
                    if (length > 0) {
                        $scope.last_cid = $scope.messages[length - 1].cmsg_id;
                    } else {
                        $scope.last_cid = null;
                    }

                    if(!isNaN(chat_id) && !$api.is_empty(chat_id))
                    {
                        $timeout(function() {
                            $scope.scrollToMessage(chat_id);
                            chat_id = null;
                        }, 1000);                                        
                    }

                    $scope.initEventHandler()
                });
                */

                $timeout(function() {
                    $scope.set_last_msg();
                }, 1500);
            }
        };

        $scope.goto_link = function(mission_id, chat_id) {
            $scope.chat_id = chat_id;
            if ($scope.mission_id == mission_id) {
                $scope.scrollToMessage(chat_id);
            }
            else {
                $stateParams.mission_id = mission_id;
                $stateParams.chat_id = chat_id;
                $scope.sync();
            }
        };
        
        $scope.sync();

        function resizeLayout() {
            var bottom = keyboardHeight + footerHeight;
            var old_bottom = parseInt(scroller.style.bottom , 10);
            if (isNaN(old_bottom))
                old_bottom = 0;

            if ($scope.files) {
                filesHeight = $scope.files.length * 25;
            } else {
                filesHeight = 0;
            }
            bottom += filesHeight;

            scroller.style.bottom = bottom + "px";

            $('#emoticon_gallery').css('bottom', footerHeight + "px");
            $('#to_users').css('bottom', footerHeight + "px");
            $('#file_bar').css('bottom', footerHeight + "px");

            var pos = viewScroll.getScrollPosition();
            pos.top += (bottom - old_bottom);

            if (pos.top < 0)
                pos.top = 0;

            viewScroll.scrollTo(pos.left, pos.top);
        }

        // I emit this event from the monospaced.elastic directive, read line 480
        $scope.$on('taResize', function(e, ta) {
            if (!ta) return;
            var taHeight = ta[0].offsetHeight;

            if (!footerBar) return;
            footerHeight = taHeight + 50;
            footerHeight = (footerHeight > 74) ? footerHeight : 74;
            
            footerBar.style.height = footerHeight + 'px';

            resizeLayout();

            try {
                var elem = angular.element(document.querySelector('#chat_view'));
                var scrollTop = viewScroll.getScrollPosition().top;
                var scrollHeight = elem[0].scrollHeight;
                var viewHeight = elem[0].offsetHeight;

                //console.log(scrollTop + "+" + viewHeight + "=" + (scrollTop + viewHeight) + "=" + scrollHeight);
                //if(scrollTop + viewHeight >= scrollHeight-400)
                //    $scope.scrollToBottom();

            }
            catch(err) {
                
            }
        });

        // keyboard processiong
        ionic.on('native.keyboardshow', onShowKeyboard, window);
        ionic.on('native.keyboardhide', onHideKeyboard, window);

        function onShowKeyboard(e) {
            if (ionic.Platform.isAndroid() && !ionic.Platform.isFullScreen) {
                return;
            }

            keyboardHeight = e.keyboardHeight || e.detail.keyboardHeight;
            resizeLayout();
        }

        function onHideKeyboard() {
            if (ionic.Platform.isAndroid() && !ionic.Platform.isFullScreen) {
                return;
            }

            keyboardHeight = 0;
            resizeLayout();
        }

        $scope.$on('$destroy', function() {
            ionic.off('native.keyboardshow', onShowKeyboard, window);
            ionic.off('native.keyboardhide', onHideKeyboard, window);
        });
    }
)

.constant('chatInputConfig', {
    append: ''
})

.directive('chatInput', [
    '$timeout', '$window', 'chatInputConfig',
    function($timeout, $window, config) {
        'use strict';

        return {
            require: 'ngModel',
            restrict: 'A, C',
            link: function(scope, element, attrs, ngModel) {

                // cache a reference to the DOM element
                var ta = element[0],
                    $ta = element;

                // ensure the element is a textarea, and browser is capable
                if (ta.nodeName !== 'TEXTAREA' || !$window.getComputedStyle) {
                    return;
                }

                // set these properties before measuring dimensions
                $ta.css({
                    'overflow': 'hidden',
                    'overflow-y': 'hidden',
                    'word-wrap': 'break-word',
                    'max-height': '100px'
                });

                // force text reflow
                var text = ta.value;
                ta.value = '';
                ta.value = text;

                var append = attrs.msdElastic ? attrs.msdElastic.replace(/\\n/g, '\n') : config.append,
                    $win = angular.element($window),
                    mirrorInitStyle = 'position: absolute; top: -999px; right: auto; bottom: auto;' +
                    'left: 0; overflow: hidden; -webkit-box-sizing: content-box;' +
                    '-moz-box-sizing: content-box; box-sizing: content-box;' +
                    'min-height: 0 !important; height: 0 !important; padding: 0;' +
                    'word-wrap: break-word; border: 0;',
                    $mirror = angular.element('<textarea tabindex="-1" ' +
                        'style="' + mirrorInitStyle + '"/>').data('elastic', true),
                    mirror = $mirror[0],
                    taStyle = getComputedStyle(ta),
                    resize = taStyle.getPropertyValue('resize'),
                    borderBox = taStyle.getPropertyValue('box-sizing') === 'border-box' ||
                    taStyle.getPropertyValue('-moz-box-sizing') === 'border-box' ||
                    taStyle.getPropertyValue('-webkit-box-sizing') === 'border-box',
                    boxOuter = !borderBox ? {
                        width: 0,
                        height: 0
                    } : {
                        width: parseInt(taStyle.getPropertyValue('border-right-width'), 10) +
                            parseInt(taStyle.getPropertyValue('padding-right'), 10) +
                            parseInt(taStyle.getPropertyValue('padding-left'), 10) +
                            parseInt(taStyle.getPropertyValue('border-left-width'), 10),
                        height: parseInt(taStyle.getPropertyValue('border-top-width'), 10) +
                            parseInt(taStyle.getPropertyValue('padding-top'), 10) +
                            parseInt(taStyle.getPropertyValue('padding-bottom'), 10) +
                            parseInt(taStyle.getPropertyValue('border-bottom-width'), 10)
                    },
                    minHeightValue = parseInt(taStyle.getPropertyValue('min-height'), 10),
                    heightValue = parseInt(taStyle.getPropertyValue('height'), 10),
                    minHeight = Math.max(minHeightValue, heightValue) - boxOuter.height,
                    maxHeight = parseInt(taStyle.getPropertyValue('max-height'), 10),
                    mirrored,
                    active,
                    copyStyle = ['font-family',
                        'font-size',
                        'font-weight',
                        'font-style',
                        'letter-spacing',
                        'line-height',
                        'text-transform',
                        'word-spacing',
                        'text-indent'
                    ];

                // exit if elastic already applied (or is the mirror element)
                if ($ta.data('elastic')) {
                    return;
                }

                // Opera returns max-height of -1 if not set
                maxHeight = maxHeight && maxHeight > 0 ? maxHeight : 9e4;

                // append mirror to the DOM
                if (mirror.parentNode !== document.body) {
                    angular.element(document.body).append(mirror);
                }

                // set resize and apply elastic
                $ta.css({
                    'resize': (resize === 'none' || resize === 'vertical') ? 'none' : 'horizontal'
                }).data('elastic', true);

                /*
                 * methods
                 */

                function initMirror() {
                    var mirrorStyle = mirrorInitStyle;

                    mirrored = ta;
                    // copy the essential styles from the textarea to the mirror
                    taStyle = getComputedStyle(ta);
                    angular.forEach(copyStyle, function(val) {
                        mirrorStyle += val + ':' + taStyle.getPropertyValue(val) + ';';
                    });
                    mirror.setAttribute('style', mirrorStyle);
                }

                function adjust() {
                    var taHeight,
                        taComputedStyleWidth,
                        mirrorHeight,
                        width,
                        overflow;

                    if (mirrored !== ta) {
                        initMirror();
                    }

                    // active flag prevents actions in function from calling adjust again
                    if (!active) {
                        active = true;

                        mirror.value = ta.value + append; // optional whitespace to improve animation
                        mirror.style.overflowY = ta.style.overflowY;

                        taHeight = ta.style.height === '' ? 'auto' : parseInt(ta.style.height, 10);

                        taComputedStyleWidth = getComputedStyle(ta).getPropertyValue('width');

                        // ensure getComputedStyle has returned a readable 'used value' pixel width
                        if (taComputedStyleWidth.substr(taComputedStyleWidth.length - 2, 2) === 'px') {
                            // update mirror width in case the textarea width has changed
                            width = parseInt(taComputedStyleWidth, 10) - boxOuter.width;
                            mirror.style.width = width + 'px';
                        }

                        mirrorHeight = mirror.scrollHeight;

                        if (mirrorHeight > maxHeight) {
                            mirrorHeight = maxHeight;
                            overflow = 'scroll';
                        } else if (mirrorHeight < minHeight) {
                            mirrorHeight = minHeight;
                        }
                        mirrorHeight += boxOuter.height;
                        ta.style.overflowY = overflow || 'hidden';

                        if (taHeight !== mirrorHeight) {
                            ta.style.height = mirrorHeight + 'px';
                            scope.$emit('elastic:resize', $ta);
                        }

                        scope.$emit('taResize', $ta); // listen to this in the UserMessagesCtrl

                        // small delay to prevent an infinite loop
                        $timeout(function() {
                            active = false;
                        }, 1);

                    }
                }

                function forceAdjust() {
                    active = false;
                    adjust();
                }

                /*
                 * initialise
                 */

                // listen
                if ('onpropertychange' in ta && 'oninput' in ta) {
                    // IE9
                    ta['oninput'] = ta.onkeyup = adjust;
                } else {
                    ta['oninput'] = adjust;
                }

                $win.bind('resize', forceAdjust);

                scope.$watch(function() {
                    return ngModel.$modelValue;
                }, function(newValue) {
                    forceAdjust();
                });

                scope.$on('elastic:adjust', function() {
                    console.log("adjust")
                    initMirror();
                    forceAdjust();
                });

                $timeout(adjust);

                /*
                 * destroy
                 */

                scope.$on('$destroy', function() {
                    $mirror.remove();
                    $win.unbind('resize', forceAdjust);
                });
            }
        };
    }
]);

function onProfilePicError(ele) {
    ele.src = ''; // set a fallback
}

function onClickAvartar(url) {
    var scope = angular.element(document.getElementById("messageView")).scope();
    scope.$apply(function () {
        scope.showUserProfile(url);
    });
}

function gotoLink(href){
    if (href != '') {
        var arr = href.split('/');
        mission_id = arr[0];
        chat_id = arr[1];
        var scope = angular.element(document.getElementById("messageView")).scope();
        scope.$apply(function () {
            scope.goto_link(mission_id, chat_id);
        });
    }
}
