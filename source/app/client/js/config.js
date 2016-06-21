(function() {
  'use strict';
  angular.module('app.config', []).constant('CONFIG', {
    BASE: "http://192.168.1.123/handcrowd/back/",
    API_BASE: "http://192.168.1.123/handcrowd/back/api/",
    AVARTAR_URL: "http://192.168.1.123/handcrowd/back/avartar/",
    GOOGLE_CONNECT_URL: "https://hc.com/back/google/connect",
    VER: "2.0"
  });

}).call(this);