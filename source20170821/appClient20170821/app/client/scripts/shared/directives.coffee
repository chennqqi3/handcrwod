
angular.module('app.directives', [])

# add background and some style just for specific page
.directive('customBackground', () ->
    return {
        restrict: "A"
        controller: [
            '$scope', '$element', '$location'
            ($scope, $element, $location) ->
                path = ->
                    return $location.path()

                addBg = (path) ->
                    # remove all the classes
                    $element.removeClass('body-home body-special body-lock')

                    # add certain class based on path
                    for p in [
                        '/404'
                        '/pages/500'
                        '/login'
                        '/signin'
                        '/signup'
                        '/signup_facebook'
                        '/signup_google'
                        '/activate'
                        '/forgotpwd'
                        '/resetpwd'
                        '/loadapp'
                        '/qr'
                    ]
                        $element.addClass('body-special').removeClass('expanded') if p == path || path.indexOf(p + "/") == 0

                    switch path
                        when '/' then $element.addClass('body-home')
                        when '/pages/lock-screen' then $element.addClass('body-special body-lock').removeClass('expanded')

                addBg( $location.path() )

                $scope.$watch(path, (newVal, oldVal) ->
                    if newVal is oldVal
                        return
                    addBg($location.path())
                )
        ]
    }
)

# switch stylesheet file
.directive('uiColorSwitch', [ ->
    return {
        restrict: 'A'
        link: (scope, ele, attrs) ->
            ele.find('.color-option').on('click', (event)->
                $this = $(this)
                hrefUrl = undefined

                style = $this.data('style')
                if style is 'loulou'
                    hrefUrl = 'styles/main.css'
                    $('link[href^="styles/main"]').attr('href',hrefUrl)
                else if style
                    style = '-' + style
                    hrefUrl = 'styles/main' + style + '.css'
                    $('link[href^="styles/main"]').attr('href',hrefUrl)
                else
                    return false

                event.preventDefault()
            )
    }
])

.directive('slimScroll', [ ->
    return {
        restrict: 'A'
        link: (scope, ele, attrs) ->
            ele.slimScroll({
                height: attrs.scrollHeight || '100%'
            })
    }
])

# history back button
.directive('goBack', [ ->
    return {
        restrict: "A"
        controller: [
            '$scope', '$element', '$window'
            ($scope, $element, $window) ->
                $element.on('click', ->
                    $window.history.back()
                )
        ]
    }
])

# Dependency: https://github.com/grevory/bootstrap-file-input
.directive('uiFileUpload', [ ->
    return {
        restrict: 'A'
        link: (scope, ele) ->
            ele.bootstrapFileInput()
    }
])

# Dependency: https://github.com/xixilive/jquery-spinner
.directive('uiSpinner', [ ->
    return {
        restrict: 'A'
        compile: (ele, attrs) -> # link and compile do not work together
            ele.addClass('ui-spinner')

            return {
                post: ->
                    ele.spinner()
            }

        # link: (scope, ele) -> # link and compile do not work together
    }

])

.directive('equals', [ ->
    return {
        restrict: 'A'
        require: '?ngModel'
        link: (scope, elem, attrs, ngModel) ->
            return if !ngModel

            scope.$watch(attrs.ngModel, () -> validate())

            attrs.$observe('equals', (val) -> validate())

            validate = () ->
                val1 = ngModel.$viewValue
                val2 = attrs.equals
                ngModel.$setValidity('equals', ! val1 || ! val2 || val1 == val2)
    }
])

.filter('htmlfy', ($sce)->
    return (text) ->
        text = text + ''
        t = text.replace(/(https?:\/\/)([\d\w\.-]+)\.([\d\w\.]{2,6})([\:][\d]+)?([\(\)\/\w \?\=\&\;\#\%\.\+\@\,\!\:-]*)*\/?/g, (url) ->
            return '<a href="' + url + '" target="_blank">' + url + '</a>'
        )
        t = t.replace(/\n/g, '<br/>')

        html = $sce.trustAsHtml(t)
        return html
)

.service('chatizeService', ($rootScope, $api) ->
    this.stripAttachString = (str) ->
        if $api.is_empty(str)
            return ''
        return str.replace(/\[file id=(\d+) url=\'([^\]]*)\'\]([^\]]*)\[\/file\]/g, (item, id, url, name) ->
            return name
        )

    this.stripLinkString = (str) ->
        if $api.is_empty(str)
            return ''

        return str.replace(/\[link href=\'([^\]]*)\'\]\[\/link\]/g, (item, href) ->
            return 'メッセージリンク'
        )

    this.stripToString = (str) ->
        if $api.is_empty(str)
            return ''

        return str.replace(/\[to:([^\]]*)\]/g, (item, user_id) ->
            return ""
        )

    this.stripQuoteString = (str) ->
        if $api.is_empty(str)
            return ''
        startIndex = str.indexOf("[引用 ")
        return str if startIndex is -1

        endIndex = str.lastIndexOf("[/引用]")
        return str if endIndex is -1

        prefix = str.substring(0, startIndex)
        suffix = str.substring(endIndex + 5)
        quote = str.substring(startIndex, endIndex + 5)

        headEndIndex = quote.indexOf("]")

        content = quote.substring(headEndIndex + 1, quote.length-5)

        str = prefix
        str += this.stripQuoteString(content)
        str += suffix

        return str

    this.strip = (str) ->
        if $api.is_empty(str)
            return ''
        str = this.stripQuoteString(str)
        str = this.stripLinkString(str)
        str = this.stripToString(str)
        str = this.stripAttachString(str)

        if !$api.is_empty(str)
            str = str.replace(/\n*/, '')
        return str

    this.emoticon = (emoticon_id) ->
        if $rootScope.emoticons && $rootScope.emoticons.length > 0
            for e in $rootScope.emoticons
                if e.emoticon_id == emoticon_id
                    return '<i class="emoticon" style="background-image:url(' + e.image + ')"></i>'
        return ''

    return this
)

.filter('chatize', ($rootScope, $api, $sce, $dateutil, CONFIG, $compile, $session)->
    return (text, hideThumb) ->
        if ($api.is_empty(text))
            return ''

        isImage = (filename) ->
            ext = filename.split('.').pop()
            return false if ext == ''
            ext = ext.toLowerCase()
            return ext == "jpg" || ext == "jpeg" || ext == "png" || ext == "bmp" || ext == "gif"

        isVideo = (filename) ->
            ext = filename.split('.').pop()
            return false if ext == ''
            ext = ext.toLowerCase()
            return ext == "mp4" || ext == "mov"

        getFileAttachString = (str) ->
            if $api.is_empty(str)
                return ''

            return str.replace(/\[file id=(\d+) url=\'([^\]]*)\'\]([^\]]*)\[\/file\]/g, (item, id, url, name) ->
                rep_str = "<div class='attach-name'>"
                if (hideThumb != true && isImage(name))
                    rep_str += getImageThumb(url, name)
                rep_str += "<i class='icon-paper-clip'></i>&nbsp;"
                if (hideThumb != true && isVideo(name))
                    urlAPI = url.replace("mattach/", "api/download/link?path=")
                    rep_str += "<a href='javascript:;' class='preview-video' preview-video='" + CONFIG.BASE + url + "'>" + name + "</a>"
                    rep_str += "<a style='margin-left: 4px;' class='btn btn-default btn-xs ' href='" + CONFIG.BASE + urlAPI + "' > ダウンロード</a>"

                else
                    urlAPI = url.replace("mattach/", "api/download/link?path=")
                    rep_str += "<a href='" + CONFIG.BASE + url + "' target='_blank'>" + name + "</a>"
                    rep_str += "<a style='margin-left: 4px;' class='btn btn-default btn-xs ' href='" + CONFIG.BASE + urlAPI + "' > ダウンロード</a>"
                rep_str += "</div>"

                return rep_str
            )

        getImageThumb = (url, name) ->
            found = name.match(/^\((\d+)x(\d+)\)/)
            w = 0
            h = 0
            if found != null
                w = found[1]
                h = found[2]

            str = "<a href='javascript:;' class='preview-image' preview-image='" + CONFIG.BASE + url + "' " + (if w>0 then ('w=' + w)) + " " + (if h>0 then ('h=' + h)) + ">"
            if w > 0 && h > 0
                if w > 300
                    h = parseInt(h * 300 / w, 10)
                    w = 300
                str += "<img src='" + CONFIG.BASE + url + "/300' style='width:" + w + "px;height:" + h + "px"
            else
                str += "<img src='" + CONFIG.BASE + url + "/300' style='max-width:300px"
            str += "'></a><br/>"
            return str

        getLinkString = (str) ->
            if $api.is_empty(str)
                return ''

            return str.replace(/\[link href=\'([^\]]*)\'\]\[\/link\]/g, (item, href) ->
                href = "#/chats/" + href
                return "<a class='btn btn-xs btn-default' href='" + href + "'><i class='icon-link'></i> メッセージリンク</a>"
            )

        getToString = (str) ->
            if $api.is_empty(str)
                return ''

            return str.replace(/\[to:([^\]]*)\]/g, (item, user_id) ->
                if (hideThumb != true)
                    return "<span class='label label-info'>TO</span><img class='img-circle avartar-mini' src='" + CONFIG.AVARTAR_URL + user_id + ".jpg'>"
                return ''
            )

        getQuoteInfo = (str) ->
            quoteInfo = (uid: null, uname: null, time: null)
            uidAttr = "id="
            uidIndex = str.indexOf(uidAttr)
            if(uidIndex != -1)
                uidIndex += uidAttr.length
                uidPart = str.substr(uidIndex)
                endIndex = uidPart.indexOf(" ")
                if(endIndex == -1)
                    endIndex = uidPart.length - 1
                quoteInfo.uid = uidPart.substring(0, endIndex)

            unameAttr = "name="
            unameIndex = str.indexOf(unameAttr)
            if(unameIndex != -1)
                unameIndex += unameAttr.length
                unamePart = str.substr(unameIndex)
                endIndex = unamePart.indexOf(" ")
                if(endIndex == -1)
                    endIndex = unamePart.length - 1
                quoteInfo.uname = unamePart.substring(0, endIndex)

            timeAttr = "time="
            timeIndex = str.indexOf(timeAttr)
            if(timeIndex != -1)
                timeIndex += timeAttr.length
                timePart = str.substr(timeIndex)
                endIndex = timePart.indexOf(" ")
                if(endIndex == -1)
                    endIndex = timePart.length - 1
                quoteInfo.time = timePart.substring(0, endIndex)

            return quoteInfo

        getOneQuoteString = (str) ->
            ret = (index: -1, str: str)

            startIndex = str.indexOf("[引用 ")
            if startIndex == -1
                return ret

            prev = str.substr(0, startIndex)
            quoteHeadIndex = str.indexOf("]", startIndex)
            quoteHead = str.substring(startIndex, quoteHeadIndex + 1)
            quoteInfo = getQuoteInfo(quoteHead)

            uid = quoteInfo.uid
            uname = quoteInfo.uname
            time = quoteInfo.time

            quoteEnd = "[/引用 time=" + time + "]"
            endIndex = str.indexOf(quoteEnd, quoteHeadIndex + 1)
            if endIndex == -1
                return ret

            content = str.substring(quoteHeadIndex + 1, endIndex)
            endIndex += quoteEnd.length
            if(str.substr(endIndex, 5) == "<br/>")
                endIndex += 5

            ret.index = endIndex

            if hideThumb != true
                if uid != null || uname != null
                    date = new Date(time * 1000);

                    years = date.getFullYear()
                    months = "0" + (date.getMonth() + 1)
                    dates = "0" + date.getDate()
                    hours = "0" + date.getHours()
                    minutes = "0" + date.getMinutes()
                    seconds = "0" + date.getSeconds()

                    dateStr = years + "-" + months.substr(-2) + "-"  + dates.substr(-2) + " " +
                            hours.substr(-2) + ":" + minutes.substr(-2) + ":" + seconds.substr(-2)
                    date = $dateutil.date_time_label(dateStr)

                    str = prev
                    str += "<div class='chat-quote'>"
                    str += "<img class='img-circle avartar-mini' src='" + CONFIG.AVARTAR_URL + uid + ".jpg'> <i class='fa fa-fw fa-quote-left'></i>"
                    str += "<div class='title'>" + uname + "<time>" + date + "</time>" + "</div>"
                    str += "<div class='content'>"
                    str += getQuoteString(content)
                    str += "</div><div class='clear'></div>"
                    str += "</div>"
                else
                    str = prev
                    str += "<div class='chat-quote no-title'>"
                    str += "<i class='fa fa-fw fa-quote-left'></i> <div class='content'>"
                    str += getQuoteString(content)
                    str += "</div><div class='clear'></div>"
                    str += "</div>"
            else
                str = prev

            ret.str = str
            return ret

        getQuoteString = (str) ->
            ret = ""

            inputStr = str
            retOne = getOneQuoteString(inputStr)
            while(retOne.index != -1)
                ret += retOne.str
                inputStr = inputStr.substr(retOne.index)
                retOne = getOneQuoteString(inputStr)

            ret += retOne.str
            return ret

        text = text + ''
        text = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/&amp;&amp;&lt;\;\;/g, '<')
            .replace(/&amp;&amp;&gt;\;\;/g, '>')
            .replace(/\n/g, '<br/>')
            .replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;')
        t = text.replace(/(https?:\/\/)([\d\w\.-]+)\.([\d\w\.]{2,6})([\:][\d]+)?([\(\)\/\w \?\=\&\;\#\%\.\+\@\,\!\:-]*)*\/?/g, (url) ->
            return '<a href="' + url + '" target="_blank">' + url + '</a>'
        )

        if $rootScope.emoticons && $rootScope.emoticons.length > 0
            l = $rootScope.emoticons.length
            for i in [0..(l-1)] # fix bug for ]:) (^^;)
                e = $rootScope.emoticons[l - i - 1]
                t = t.replace(e.exp, '<i class="emoticon" style="background-image:url(' + e.image + ')"></i>')

        t = getFileAttachString(t)
        t = getLinkString(t)
        t = getQuoteString(t)
        t = getToString(t)

        html = $sce.trustAsHtml(t)

        return html
)

.filter('noHTML', [ ->
    return (text) ->
        return '' if text == null
        return '' if text == undefined
        text = text + ''
        return text
            .replace(/&/g, '&amp;')
            .replace(/>/g, '&gt;')
            .replace(/</g, '&lt;')
])

.directive('enterSubmit', [ ->
    return {
        restrict: 'A'
        link: (scope, elem, attrs) ->
            elem.bind('keydown', (event) ->
                code = event.keyCode || event.which

                if (code == 13 && (event.shiftKey || event.ctrlKey))
                    event.preventDefault()
                    scope.$apply(attrs.enterSubmit)

                if (code == 27)
                    scope.$apply(attrs.esc)
            )
    }
])

.directive('scrollTop', ($timeout) ->
    return {
        restrict: 'A'
        link: (scope, elem, attrs) ->
            scrollTop = 0
            scrollHeight = 0
            postedScrolledTop = false

            elem.on('scroll', ->
                curScrollHeight = elem[0].scrollHeight - elem.outerHeight()
                curScrollTop = elem.scrollTop()
                if curScrollHeight > 0
                    if curScrollTop == 0
                        $timeout(->
                            if curScrollTop == 0 && !postedScrolledTop
                                scope.$apply(attrs.scrollTop)

                                # fix bug for iPad safari
                                postedScrolledTop = true
                                $timeout(->
                                    postedScrolledTop = false
                                , 1000)
                        , 1000)

            )
    }
)

.directive('scrollBottom', ($timeout) ->
    return {
        restrict: 'A'
        link: (scope, elem, attrs) ->
            scrollTop = 0
            scrollHeight = 0
            postedScrolledBottom = false

            elem.on('scroll', ->
                curScrollHeight = elem[0].scrollHeight - elem.outerHeight()
                curScrollTop = elem.scrollTop()
                if curScrollHeight > 0
                    if curScrollTop >= curScrollHeight
                        $timeout(->
                            if curScrollTop >= curScrollHeight && !postedScrolledBottom
                                scope.$apply(attrs.scrollBottom)

                                # fix bug for iPad safari
                                postedScrolledBottom = true
                                $timeout(->
                                    postedScrolledBottom = false
                                , 1000)
                        , 1000)

            )
    }
)

.directive('scroll', ($timeout) ->
    return {
        restrict: 'A'
        link: (scope, elem, attrs) ->
            elem.on('scroll', ->
                scope.$apply(attrs.scroll)
            )
    }
)

.directive('autoFocus', [
    '$timeout'
    ($timeout) ->
        return {
            link: (scope, ele, attrs) ->
                scope.$watch(attrs.autoFocus, (newVal) ->
                    if newVal
                        $timeout( ->
                            ele[0].focus()
                        , 0, false)
                )
        }
])

# filters
.filter('date_label',
    ($dateutil) ->
        return (input) ->
            return $dateutil.date_label(input)
)

.filter('min_date_label',
    ($dateutil) ->
        return (input) ->
            return $dateutil.min_date_label(input)
)

.filter('times_label',
    ($dateutil) ->
        return (input) ->
            return $dateutil.times_label(input)
)

.filter('hours_label',
    ($dateutil) ->
        return (input) ->
            return $dateutil.hours_label(input)
)

.filter('date_time_label',
    ($dateutil) ->
        return (input) ->
            return $dateutil.date_time_label(input)
)

.filter('home_label',
    ($rootScope) ->
        return (input) ->
            if $rootScope.cur_home == null
                return 'グループなし'
            else
                return input
)

.filter('sir_label',
    ($rootScope) ->
        return (input) ->
            if input == null || input == '' || input == '全員'
                return input
            else
                return input + 'さん'
)

.filter('abbr',
    ($rootScope) ->
        return (input) ->
            abbr = input.substr(0, 1)
            if abbr >= 'A' && abbr <= 'Z' || abbr >= 'a' && abbr <= 'z' || abbr >= '0' && abbr <= '9'
                abbr = input.substr(0, 2)
            return abbr
)

.filter('room_member_label',
    ($rootScope) ->
        return (input) ->
            return $rootScope.get_rpriv_name(input)
)
