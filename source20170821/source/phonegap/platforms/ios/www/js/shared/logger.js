'use strict';

angular.module('app.logger', [])
.factory('logger', 
    function($cordovaToast) {
        var logIt;
        /*
        toastr.options = {
            "closeButton": true,
            "positionClass": "toast-bottom-right",
            "timeOut": "3000"
        };*/
        logIt = function(message, type) {
            try {
                $cordovaToast.show(message, 'short', 'center');
            }
            catch(e) {
                
            }
        };
        return {
            log: function(message) {
                logIt(message, 'info');
            },
            logWarning: function(message) {
                logIt(message, 'warning');
            },
            logSuccess: function(message) {
                logIt(message, 'success');
            },
            logError: function(message) {
                logIt(message, 'error');
            }
        };
    }
);