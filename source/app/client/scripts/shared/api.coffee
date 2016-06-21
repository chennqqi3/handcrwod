angular.module('app.api', [])

.factory('$api', 
    ($rootScope, $http, $session, $upload, logger, CONFIG, $location) ->
        call_api = (url, data) ->
            data = data || {}
            data.TOKEN = $session.getTOKEN() if url != 'user/signin' && is_empty(data.TOKEN)
            #show_spinner_bar()

            req = 
                method: 'POST'
                cache: false
                url: CONFIG.API_BASE + url
                headers: 
                   'Content-Type': undefined
                data: data

            $http(req)
                .error((data, status, headers, config) ->
                    logger.logError('エラーで読み込めませんでした。ページを更新して下さい。')
                    #hide_spinner_bar()
                    $rootScope.g_finished_ajax = true
                )

        upload_file = (url, file, params) ->
            data = {}
            if params != undefined
                data = params
            data.TOKEN = $session.getTOKEN()

            $upload.upload(
                url: CONFIG.API_BASE + url
                data: data
                file: file
            )

        cancel_upload = (upload) ->
            upload.abort()
            return

        import_csv = (home_id, file) ->
            $upload.upload({
                url: CONFIG.API_BASE + 'mission/import_csv'
                data: { TOKEN: $session.getTOKEN(), home_id: home_id },
                file: file
                });

        is_empty = (value) ->
            return value == null || value == undefined || value == "" || value == 0

        get_base_url = ->
            url = $location.absUrl()
            return url.substr(0, url.lastIndexOf("#"))

        init_notification = ->
            try
                if (Notification.permission != "granted")
                    Notification.requestPermission()
            catch error
                return

            return

        show_notification = (img, title, text, path) ->
            try
                if text.length > 30
                    text = text.substr(0, 30)
                    text = text.replace(/\n/g, ' ')
                    text = text + ' ...'
                notification = new Notification(title, 
                    icon: img,
                    body: text
                )

                notification.onclick = ->
                    if path != undefined
                        $location.path(path)
                        window.focus()
            catch e
                # ...
            
            return

        return {
            call: call_api
            upload_file: upload_file
            cancel_upload: cancel_upload
            import_csv: import_csv
            is_empty: is_empty
            base_url: get_base_url
            init_notification: init_notification
            show_notification: show_notification
        }
)

.factory('$numutil', 
    (logger) ->
        to_num = (num_str) ->
            if num_str != "" or num_str != null or num_str == NaN
                num_str = num_str + ""
                num_str = num_str.replace(/[．。]+/g, ".")
                num_str = num_str.replace(/０/g, "0")
                num_str = num_str.replace(/１/g, "1")
                num_str = num_str.replace(/２/g, "2")
                num_str = num_str.replace(/３/g, "3")
                num_str = num_str.replace(/４/g, "4")
                num_str = num_str.replace(/５/g, "5")
                num_str = num_str.replace(/６/g, "6")
                num_str = num_str.replace(/７/g, "7")
                num_str = num_str.replace(/８/g, "8")
                num_str = num_str.replace(/９/g, "9")
                num_str = num_str.replace(/,/g, "")
                return num_str * 1
            else
                return ""

        to_decimal = (v, places) ->
            return v if isNaN(v)
            factor = "1" + Array(+(places > 0 && places + 1)).join("0");
            return Math.round(v * factor) / factor;

        return {
            to_num: to_num
            to_decimal: to_decimal
        }
)