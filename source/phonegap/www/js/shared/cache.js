(function() {
  angular.module('app.cache', []).factory('$cache', function($rootScope, $http, $session, logger, CONFIG, $location) {
    var call_api, get_message, set_message;
    call_api = function(url, data) {
      var req;
      data = data || {};
      req = {
        method: 'POST',
        cache: false,
        url: $rootScope.cache_uri + url,
        headers: {
          'Content-Type': void 0
        },
        data: data
      };
      return $http(req).error(function(data, status, headers, config) {
        return logger.logError('エラーで読み込めませんでした。ページを更新して下さい。');
      });
    };
    set_message = function(cache_id, content, callback) {
      var params, url;
      params = {
        content: content
      };
      url = 'ms/';
      if (cache_id !== null) {
        url += cache_id;
      }
      call_api(url, params).then(function(res) {
        if (callback !== void 0) {
          return callback(res.data);
        }
      });
    };
    get_message = function(cache_id, callback) {
      call_api('mg/' + cache_id).then(function(res) {
        if (callback !== void 0) {
          return callback(res.data);
        }
      });
    };
    return {
      set_message: set_message,
      get_message: get_message
    };
  });

}).call(this);
