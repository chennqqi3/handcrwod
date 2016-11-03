
angular.module('app.directives', [])

.directive('equals', function() {
    return {
        restrict: 'A',
        require: '?ngModel',
        link: function(scope, elem, attrs, ngModel) {
            if (!ngModel)
                return;

            scope.$watch(attrs.ngModel, function() { validate(); });

            attrs.$observe('equals', function(val) { validate(); });

            validate = function() {
                val1 = ngModel.$viewValue;
                val2 = attrs.equals;
                ngModel.$setValidity('equals', ! val1 || ! val2 || val1 == val2);
            }
        }
    }
})

.filter('htmlfy', function($sce) {
    return function(text) {
        text = text + '';
        t = text.replace(/(https?:\/\/)?([\da-z\.-]+)\.([\da-z\.]{2,6})([\:][\d]+)?([\/\w \?\=\&\;\#\%\.-]*)*\/?/g, 
            function(url) {
                return '<a href="#" onclick="window.open(\'' + url + '\', \'_system\', \'location=yes\'); return false;">' + url + '</a>';
            }
        );
        t = t.replace(/\n/g, '<br/>');
         
        html = $sce.trustAsHtml(t);
        return html;
    }
})

.filter('noHTML', function() {
    return function(text) {
        if (text == null)
            return '';
        if (text == undefined)
            return '';
        text = text + '';
        return text.replace(/&/g, '&amp;')
            .replace(/>/g, '&gt;')
            .replace(/</g, '&lt;');
    }
})

.service('chatizeService', function($api, $rootScope) {
    this.stripAttachString = function(str) {
        if ($api.is_empty(str)) {
            return '';
        }
        return str.replace(/\[file id=(\d+) url=\'([^\]]*)\'\]([^\]]*)\[\/file\]/g, function(item, id, url, name) {
            return name;
        });
    };
    this.stripLinkString = function(str) {
        if ($api.is_empty(str)) {
            return '';
        }
        return str.replace(/\[link href=\'([^\]]*)\'\]\[\/link\]/g, function(item, href) {
            return 'メッセージリンク';
        });
    };
    this.stripToString = function(str) {
        if ($api.is_empty(str)) {
            return '';
        }
        return str.replace(/\[to:([^\]]*)\]/g, function(item, user_id) {
            return "";
        });
    };
    this.stripQuoteString = function(str) {
        if ($api.is_empty(str))
            return '';
        startIndex = str.indexOf("[引用 ");
        if (startIndex == -1)
            return str;

        endIndex = str.lastIndexOf("[/引用]");
        if (endIndex == -1)
            return str;

        prefix = str.substring(0, startIndex);
        suffix = str.substring(endIndex + 5);
        quote = str.substring(startIndex, endIndex + 5);

        headEndIndex = quote.indexOf("]");

        content = quote.substring(headEndIndex + 1, quote.length - 5);

        str = prefix;
        str += this.stripQuoteString(content);
        str += suffix;
        
        return str;
    };
    this.strip = function(str) {
        if ($api.is_empty(str))
            return '';
        
        str = this.stripQuoteString(str);
        str = this.stripLinkString(str);
        str = this.stripToString(str);
        str = this.stripAttachString(str);

        if (!$api.is_empty(str))
            str = str.replace(/\n*/, '');
        
        return str;
    };

    this.emoticon = function(emoticon_id) {
        if ($rootScope.emoticons) {
            for(var i=0; i<$rootScope.emoticons.length; i++){
                e = $rootScope.emoticons[i];
                if (e.emoticon_id == emoticon_id)
                    return '<i class="emoticon" style="background-image:url(' + e.image + ')"></i>';
            }
        }
        return '';
    };

    return this;
})

.filter('chatize', 
    function($api, $sce, $dateutil, CONFIG, $compile, $session, $rootScope) {
        return function(text, hideThumb) {
            if ($api.is_empty(text))
                return '';
            
            isImage = function(filename) {
                var ext = filename.split('.').pop();
                if (ext == '')
                    return false;
                
                ext = ext.toLowerCase();
                return ext === "jpg" || ext === "jpeg" || ext === "png" || ext === "bmp" || ext === "gif";
            };
            getFileAttachString = function(str) {
                if ($api.is_empty(str))
                    return '';
                return str.replace(/\[file id=(\d+) url=\'([^\]]*)\'\]([^\]]*)\[\/file\]/g, function(item, id, url, name) {
                    var rep_str;
                    rep_str = "<div class='attach-name'>";
                    if (hideThumb != true && isImage(name))
                        rep_str += "<a href='javascript:;' class='preview-image' preview-image='" + CONFIG.BASE + url + "'><img src='" + CONFIG.BASE + url + "/150' style='max-width:150px'></a><br/>";                
                    rep_str += "<i class='icon-paper-clip'></i>&nbsp;";
                    rep_str += '<a href="#" onclick="window.open(\'' + CONFIG.API_BASE + url + '\', \'_system\', \'location=yes\'); return false;">' + name + "</a>";
                    rep_str += "</div>";
                    return rep_str;
                });
            };
            getLinkString = function(str) {
                if ($api.is_empty(str))
                    return '';
                return str.replace(/\[link href=\'([^\]]*)\'\]\[\/link\]/g, function(item, href) {
                    
                    return "<a class='btn btn-xs btn-default' href='javascript:;' onclick='gotoLink(\"" + href + "\")'><i class='icon-link'></i> メッセージリンク</a>";
                });
            };
            getToString = function(str) {
                if ($api.is_empty(str))
                    return '';
                return str.replace(/\[to:([^\]]*)\]/g, function(item, user_id) {
                    if (hideThumb != true)
                        return "<span class='label label-info'>TO</span><img class='img-circle avartar-mini' src='" + CONFIG.AVARTAR_URL + user_id + ".jpg'>";
                    return '';                    
                });
            };
            getQuoteInfo = function(str) {
                var endIndex, quoteInfo, timeAttr, timeIndex, timePart, uidAttr, uidIndex, uidPart, unameAttr, unameIndex, unamePart;
                quoteInfo = {
                    uid: null,
                    uname: null,
                    time: null
                };
                uidAttr = "id=";
                uidIndex = str.indexOf(uidAttr);
                if (uidIndex !== -1) {
                    uidIndex += uidAttr.length;
                    uidPart = str.substr(uidIndex);
                    endIndex = uidPart.indexOf(" ");
                    if (endIndex === -1) {
                        endIndex = uidPart.length - 1;
                    }
                    quoteInfo.uid = uidPart.substring(0, endIndex);
                }
                unameAttr = "name=";
                unameIndex = str.indexOf(unameAttr);
                if (unameIndex !== -1) {
                    unameIndex += unameAttr.length;
                    unamePart = str.substr(unameIndex);
                    endIndex = unamePart.indexOf(" ");
                    if (endIndex === -1) {
                        endIndex = unamePart.length - 1;
                    }
                    quoteInfo.uname = unamePart.substring(0, endIndex);
                }
                timeAttr = "time=";
                timeIndex = str.indexOf(timeAttr);
                if (timeIndex !== -1) {
                    timeIndex += timeAttr.length;
                    timePart = str.substr(timeIndex);
                    endIndex = timePart.indexOf(" ");
                    if (endIndex === -1) {
                        endIndex = timePart.length - 1;
                    }
                    quoteInfo.time = timePart.substring(0, endIndex);
                }
                return quoteInfo;
            };
            getOneQuoteString = function(str) {
                var content, date, dateStr, dates, endIndex, hours, minutes, months, prev, quoteEnd, quoteHead, quoteHeadIndex, quoteInfo, ret, seconds, startIndex, time, uid, uname, years;
                ret = {
                    index: -1,
                    str: str
                };
                startIndex = str.indexOf("[引用 ");
                if (startIndex === -1) {
                    return ret;
                }
                prev = str.substr(0, startIndex);
                quoteHeadIndex = str.indexOf("]", startIndex);
                quoteHead = str.substring(startIndex, quoteHeadIndex + 1);
                quoteInfo = getQuoteInfo(quoteHead);
                uid = quoteInfo.uid;
                uname = quoteInfo.uname;
                time = quoteInfo.time;
                quoteEnd = "[/引用 time=" + time + "]";
                endIndex = str.indexOf(quoteEnd, quoteHeadIndex + 1);
                if (endIndex === -1) {
                    return ret;
                }
                content = str.substring(quoteHeadIndex + 1, endIndex);
                endIndex += quoteEnd.length;
                if (str.substr(endIndex, 5) === "<br/>") {
                    endIndex += 5;
                }
                ret.index = endIndex;
                if (hideThumb != true) {
                    if (uid !== null || uname !== null) {
                        date = new Date(time * 1000);
                        years = date.getFullYear();
                        months = "0" + (date.getMonth() + 1);
                        dates = "0" + date.getDate();
                        hours = "0" + date.getHours();
                        minutes = "0" + date.getMinutes();
                        seconds = "0" + date.getSeconds();
                        dateStr = years + "-" + months.substr(-2) + "-" + dates.substr(-2) + " " + hours.substr(-2) + ":" + minutes.substr(-2) + ":" + seconds.substr(-2);
                        date = $dateutil.date_time_label(dateStr);
                        str = prev;
                        str += "<div class='chat-quote'>";
                        str += "<img class='img-circle avartar-mini' src='" + CONFIG.AVARTAR_URL + uid + ".jpg'> <i class='fa fa-fw fa-quote-left'></i>";
                        str += "<div class='title'>" + uname + "<time>" + date + "</time>" + "</div>";
                        str += "<div class='content'>";
                        str += getQuoteString(content);
                        str += "</div><div class='clear'></div>";
                        str += "</div>";
                    } else {
                        str = prev;
                        str += "<div class='chat-quote no-title'>";
                        str += "<i class='fa fa-fw fa-quote-left'></i> <div class='content'>";
                        str += getQuoteString(content);
                        str += "</div><div class='clear'></div>";
                        str += "</div>";
                    }
                }
                else {
                    str = prev;
                }
                ret.str = str;
                return ret;
            };
            getQuoteString = function(str) {
                var inputStr, ret, retOne;
                ret = "";
                inputStr = str;
                retOne = getOneQuoteString(inputStr);
                while (retOne.index !== -1) {
                    ret += retOne.str;
                    inputStr = inputStr.substr(retOne.index);
                    retOne = getOneQuoteString(inputStr);
                }
                ret += retOne.str;
                return ret;
            };
            text = text + '';
            text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br/>');
            t = text.replace(/(https?:\/\/)([\d\w\.-]+)\.([\d\w\.]{2,6})([\(\)\/\w \?\=\&\;\#\%\.\+\@\,\!\:-]*)*\/?/g, function(url) {
                return '<a href="#" onclick="window.open(\'' + url + '\', \'_system\', \'location=yes\'); return false;">' + url + '</a>';
            });

            if ($rootScope.emoticons) {
                l = $rootScope.emoticons.length
                for (i =0; i < l; i ++) { // fix bug for ]:) (^^;)
                    e = $rootScope.emoticons[l - i - 1];
                    t = t.replace(e.exp, '<i class="emoticon" style="background-image:url(' + e.image + ')"></i>');
                }    
            }

            t = getFileAttachString(t);
            t = getLinkString(t);
            t = getQuoteString(t);
            t = getToString(t);
            html = $sce.trustAsHtml(t);
            return html;
        };
    }
)

.directive('enterSubmit', 
    function() {
        return {
            restrict: 'A',
            link: function(scope, elem, attrs) {
                return elem.bind('keydown', function(event) {
                    var code;
                    code = event.keyCode || event.which;
                    if (code === 13 && (event.shiftKey || event.ctrlKey)) {
                        event.preventDefault();
                        scope.$apply(attrs.enterSubmit);
                    }
                    if (code === 27) {
                        return scope.$apply(attrs.esc);
                    }
                });
            }
        };
    }
)

.directive('enterSearch', 
    function() {
        return {
            restrict: 'A',
            link: function(scope, elem, attrs) {
                return elem.bind('keydown keypress', function(event) {
                    var code;
                    code = event.keyCode || event.which;
                    if (code === 13) {
                        event.preventDefault();
                        scope.$apply(attrs.enterSearch);
                    }
                });
            }
        };
    }
)

.directive('scrollTop', 
    function($timeout) {
        return {
            restrict: 'A',
            link: function(scope, elem, attrs) {
                var postedScrolledTop, scrollHeight, scrollTop;
                scrollTop = 0;
                scrollHeight = 0;
                postedScrolledTop = false;
                return elem.on('scroll', function() {
                    var curScrollHeight, curScrollTop;
                    /*
                    curScrollHeight = elem[0].scrollHeight - elem.outerHeight();
                    curScrollTop = elem.scrollTop();
                    if (curScrollHeight > 0) {
                        if (curScrollTop === 0) {
                            return $timeout(function() {
                                if (curScrollTop === 0 && !postedScrolledTop) {
                                    scope.$apply(attrs.scrollTop);
                                    postedScrolledTop = true;
                                    return $timeout(function() {
                                        return postedScrolledTop = false;
                                    }, 1000);
                                }
                            }, 1000);
                        }
                    }
                    */
                });
            }
        };
    }
)

.directive('scrollBottom', 
    function($timeout) {
        return {
            restrict: 'A',
            link: function(scope, elem, attrs) {
                var postedScrolledBottom, scrollHeight, scrollTop;
                scrollTop = 0;
                scrollHeight = 0;
                postedScrolledBottom = false;
                return elem.on('scroll', function() {
                    var curScrollHeight, curScrollTop;
                    /*
                    curScrollHeight = elem[0].scrollHeight - elem.outerHeight();
                    curScrollTop = elem.scrollTop();
                    if (curScrollHeight > 0) {
                        if (curScrollTop >= curScrollHeight) {
                            return $timeout(function() {
                                if (curScrollTop >= curScrollHeight && !postedScrolledBottom) {
                                    scope.$apply(attrs.scrollBottom);
                                    postedScrolledBottom = true;
                                    return $timeout(function() {
                                        return postedScrolledBottom = false;
                                    }, 1000);
                                }
                            }, 1000);
                        }
                    }*/
                });
            }
        };
    }
)

.directive('appVersion', function () {
    return function(scope, elm, attrs) {
        try {         
            cordova.getAppVersion(function (version) {
                elm.text(version);
            });   
        }
        catch(err) {

        }
    };
})