(function() {
  'use strict';
  angular.module('app.config', []).constant('CONFIG', {
    BASE: "http://localhost/handcrowd/back/",
    API_BASE: "http://localhost/handcrowd/back/api/",
    AVARTAR_URL: "http://localhost/handcrowd/back/avartar/",
    GOOGLE_CONNECT_URL: "https://hc.com/back/google/connect",
    VER: "2.0"
  });

}).call(this);
