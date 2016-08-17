angular.module('app.api', [])

.factory('$api', 
    function($http, $session, $upload, logger, CONFIG, $location, $rootScope, $ionicLoading) {
        var call_api, get_base_url, import_csv, is_empty, get_mobile_operating_system;
        
        call_api = function(url, data) {
            var req;
            data = data || {};
            if (url !== 'user/signin' && is_empty(data.TOKEN)) {
                data.TOKEN = $session.getTOKEN();
            }
            req = {
                method: 'POST',
                cache: false,
                url: CONFIG.API_BASE + url,
                headers: {
                    'Content-Type': void 0
                },
                data: data
            };
            return $http(req).error(function(data, status, headers, config) {
                logger.logError('エラーで読み込めませんでした。ページを更新して下さい。');
                $rootScope.g_finished_ajax = true;
                hide_waiting();
            });
        };
        
        upload_file = function(url, file, params) {
            data = {};
            if (params != undefined)
                data = params;
            data.TOKEN = $session.getTOKEN();

            return $upload.upload({
                url: CONFIG.API_BASE + url,
                data: data,
                file: file,
            });
        };

        upload_file2 = function(url, file, params) { // FileTransfer mode
            data = {};
            if (params != undefined)
                data = params;
            data.TOKEN = $session.getTOKEN();

            options = new FileUploadOptions();
            options.fileKey = "file";
            options.fileName = file.name;
            options.params = data;

            ft = new FileTransfer();
            if (file.onProgress)
                ft.onprogress = file.onProgress;
            ft.upload(file.fullPath,
                CONFIG.API_BASE + url,
                function (result) {
                    if (file.onSuccess) {
                        if (result.response) {                         
                            res = JSON.parse(result.response);   
                            file.onSuccess(res);  
                        } 
                    }
                },
                function (error) {
                    if (file.onError)
                        file.onError(error);
                },
                options
            )

            return ft;
        };

        cancel_upload = function(upload) {
            upload.abort();
        };
        
        import_csv = function(file) {
            return $upload.upload({
                url: CONFIG.API_BASE + 'mission/import_csv',
                data: {
                    TOKEN: $session.getTOKEN()
                },
                file: file
            });
        };
        
        is_empty = function(value) {
            return value === null || value === void 0 || value === "" || value === 0;
        };
        
        get_base_url = function() {
            var url;
            url = $location.absUrl();
            return url.substr(0, url.lastIndexOf("#"));
        };
        
        get_mobile_operating_system = function() {
            var userAgent = navigator.userAgent;

            if( userAgent.match( /iPad/i ) != null || userAgent.match( /iPhone/i ) != null || userAgent.match( /iPod/i ) != null )
            {
                return 'iOS';
            }
            else if( userAgent.match( /Android/i ) != null )
            {
                return 'Android';
            }
            else
            {
                return 'unknown';
            }
        };

        get_device_type = function() {
            var os = get_mobile_operating_system();

            if(os == "iOS")
                device_type = 1;
            else if(os == "Android")
                device_type = 2;
            else
                device_type = 0;

            return device_type;
        };

        show_waiting = function(msg) {
            if (msg == undefined)
                msg = '処理中です。しばらくお待ちください...';
            $ionicLoading.show({
                template: '<ion-spinner icon="ios-small"></ion-spinner><p>' + msg + '</p>'
            });
        };

        hide_waiting = function() {
            $ionicLoading.hide();
        };

        qr_image_url = function(url, size) {
            if (size == undefined)
                size = 300;
            return "http://chart.apis.google.com/chart?cht=qr&chs=" + size + "x" + size + "&chl=" + encodeURIComponent(url) + "&chld=H|0";
        };
            
        init_emoticon = function(icon) {
            icon.image = CONFIG.BASE + icon.image;
            icon.exp = icon.alt.replace(/\)/g, '\\)');
            icon.exp = icon.exp.replace(/\(/g, '\\(');
            icon.exp = icon.exp.replace(/\:/g, '\\:');
            icon.exp = icon.exp.replace(/\|/g, '\\|');
            icon.exp = icon.exp.replace(/\*/g, '\\*');
            icon.exp = icon.exp.replace(/\^/g, '\\^');
            icon.exp = new RegExp(icon.exp, 'g');
        }

        return {
            call: call_api,
            upload_file: upload_file,
            upload_file2: upload_file2,
            cancel_upload: cancel_upload,
            import_csv: import_csv,
            is_empty: is_empty,
            base_url: get_base_url,
            mobile_operating_system: get_mobile_operating_system,
            device_type: get_device_type,
            show_waiting: show_waiting,
            hide_waiting: hide_waiting,
            qr_image_url: qr_image_url,
            init_emoticon: init_emoticon
        };
    }
)

.factory('$numutil',
    function(logger) {
        var to_decimal, to_num;
        to_num = function(num_str) {
            if (num_str !== "" || num_str !== null || num_str === NaN) {
                num_str = num_str + "";
                num_str = num_str.replace(/[．。]+/g, ".");
                num_str = num_str.replace(/０/g, "0");
                num_str = num_str.replace(/１/g, "1");
                num_str = num_str.replace(/２/g, "2");
                num_str = num_str.replace(/３/g, "3");
                num_str = num_str.replace(/４/g, "4");
                num_str = num_str.replace(/５/g, "5");
                num_str = num_str.replace(/６/g, "6");
                num_str = num_str.replace(/７/g, "7");
                num_str = num_str.replace(/８/g, "8");
                num_str = num_str.replace(/９/g, "9");
                num_str = num_str.replace(/,/g, "");
                return num_str * 1;
            } else {
                return "";
            }
        };
        to_decimal = function(v, places) {
            var factor;
            if (isNaN(v)) {
                return v;
            }
            factor = "1" + Array(+(places > 0 && places + 1)).join("0");
            return Math.round(v * factor) / factor;
        };
        return {
            to_num: to_num,
            to_decimal: to_decimal
        };
    }
);