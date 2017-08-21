angular.module('app.cache', [])

.factory('$cache', 
    ($rootScope, $http, $session, logger, CONFIG, $location) ->
        call_api = (url, data, method) ->
            data = data || {}
            method = method || 'POST'

            req = 
                method: method
                url: $rootScope.cache_uri + url
                headers: 
                   'Content-Type': undefined
                data: data

            $http(req)
                .error((data, status, headers, config) ->
                    logger.logError('エラーで読み込めませんでした。ページを更新して下さい。')
                )

        set_message = (cache_id, content, callback) ->
            params =
                content: content
            url = 'ms/'
            if cache_id != null
                url += cache_id
            call_api(url, params)
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        get_message = (cache_id, callback) ->
            call_api('mg/' + cache_id, null, 'GET')
                .then((res) ->
                    if callback != undefined
                        callback(res.data)
                )
            return

        return {
            set_message: set_message
            get_message: get_message
        }
)