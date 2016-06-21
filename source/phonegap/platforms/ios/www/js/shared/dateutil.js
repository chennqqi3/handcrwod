angular.module('app.dateutil', [])

.factory('$dateutil', [
    '$session', 'numberFilter', '$numutil', function($session, numberFilter, $numutil) {
        var check_is_past, get_date_label, get_date_time_label, get_hours_label, get_min_date_label, get_times_label, std_date_string, twoDigit;
        get_date_label = function(date) {
            var d, diff, dt, format, now, sod;
            if (date === null || date === void 0) {
                return '';
            }
            dt = moment.tz(date, $session.time_zone);
            now = moment.tz($session.time_zone);
            sod = now.startOf('day');
            diff = dt.diff(sod, 'days', true);
            if (diff < -6) {
                format = 'sameElse';
            } else if (diff < -1) {
                format = 'lastWeek';
            } else if (diff < 0) {
                format = 'lastDay';
            } else if (diff < 1) {
                format = 'sameDay';
            } else if (diff < 2) {
                format = 'nextDay';
            } else if (diff < 7) {
                format = 'nextWeek';
            } else {
                format = 'sameElse';
            }
            format = dt.localeData().calendar(format, dt, moment(now));
            format = format.replace(/\ LT$/, '');
            d = dt.format(format);
            if (d === 'Invalid date') {
                d = '';
            }
            return dt.format(format);
        };
        get_min_date_label = function(date) {
            var d, diff, dt, format, now, sod;
            if (date === null || date === void 0) {
                return '';
            }
            dt = moment.tz(date, $session.time_zone);
            now = moment.tz($session.time_zone);
            sod = now.startOf('day');
            diff = dt.diff(sod, 'days', true);
            if (diff < -6) {
                format = 'L';
            } else if (diff < -1) {
                format = 'lastWeek';
            } else if (diff < 0) {
                format = 'lastDay';
            } else if (diff < 1) {
                format = 'sameDay';
            } else if (diff < 2) {
                format = 'nextDay';
            } else if (diff < 7) {
                format = 'nextWeek';
            } else {
                format = 'L';
            }
            if (format !== "L") {
                format = dt.localeData().calendar(format, dt, moment(now));
                format = format.replace(/\ LT$/, '');
            }
            d = dt.format(format);
            if (d === 'Invalid date') {
                d = '';
            }
            return dt.format(format);
        };
        get_date_time_label = function(date) {
            var diff, dt, format, lbl, now, sod;
            if (date === null || date === void 0) {
                return '';
            }
            date = date.replace(/\ /gi, 'T');
            dt = moment.tz(date, $session.time_zone);
            now = moment.tz($session.time_zone);
            sod = now.startOf('day');
            diff = dt.diff(sod, 'days', true);
            if (diff < -6) {
                format = 'sameElse';
            } else if (diff < -1) {
                format = 'lastWeek';
            } else if (diff < 0) {
                format = 'lastDay';
            } else if (diff < 1) {
                format = 'sameDay';
            } else if (diff < 2) {
                format = 'nextDay';
            } else if (diff < 7) {
                format = 'nextWeek';
            } else {
                format = 'sameElse';
            }
            format = dt.localeData().calendar(format, dt, moment(now));
            format = format.replace(/\ LT$/, '');
            format = format + " LT";
            lbl = dt.format(format);
            return lbl;
        };
        get_time_label = function(date) {
            var diff, dt, format, lbl, now, sod;
            if (date === null || date === void 0) {
                return '';
            }
            date = date.replace(/\ /gi, 'T');
            dt = moment.tz(date, $session.time_zone);
            now = moment.tz($session.time_zone);
            lbl = dt.format("LT");
            return lbl;
        };
        check_is_past = function(date) {
            if (date === null || date === void 0) {
                return false;
            }
            return moment.tz(date, $session.time_zone).isBefore(moment());
        };
        get_times_label = function(hours) {
            var day, hour;
            if (hours === null || hours === void 0 || hours === NaN) {
                return '';
            }
            day = Math.floor(hours / 8);
            hour = $numutil.to_decimal(hours - day * 8, 2);
            if (day === 0) {
                return hour + '時間';
            }
            if (hour === 0) {
                return numberFilter(day) + '人日';
            }
            return numberFilter(day) + '人日' + hour + '時間';
        };
        get_hours_label = function(hours) {
            if (hours === null || hours === void 0 || hours === NaN) {
                return '';
            }
            return numberFilter(hours) + '時間';
        };
        std_date_string = function(date) {
            return date.getFullYear() + "-" + twoDigit(date.getMonth() + 1) + "-" + twoDigit(date.getDate());
        };    

        std_date_time_string = function(datetime) {
            return datetime.getFullYear() + "-" + twoDigit(datetime.getMonth() + 1) + "-" + twoDigit(datetime.getDate()) + " " + twoDigit(datetime.getHours()) + ":" + twoDigit(datetime.getMinutes()) + ":" + twoDigit(datetime.getSeconds());
        };
        
        twoDigit = function(val) {
            if (!isNaN(val) && val.toString().length === 1) {
                return "0" + val;
            } else {
                return val;
            }
        };    

        get_ellipsis_time_str = function(date_time_str, compare_date_time_str) {
            var ret_str = date_time_str;

            if(!compare_date_time_str)
                return get_date_time_label(ret_str);

            var time = moment.tz(date_time_str, $session.time_zone);
            var year = time.year();
            var month = time.month() + 1;
            var date = time.date();
            var hours = time.hours();
            var minutes = time.minutes();
            var hours = time.hours();
            var minutes = time.minutes();

            var comp_time = moment.tz(compare_date_time_str, $session.time_zone);
            var comp_year = comp_time.year();
            var comp_month = comp_time.month() + 1;
            var comp_date = comp_time.date();
            var comp_hours = comp_time.hours();
            var comp_minutes = comp_time.minutes();

            var now = moment();
            var sod = now.startOf('day');
            var diff = time.diff(sod, 'days', true);

            if(year == comp_year)
            {
                if(month == comp_month && date == comp_date)
                {
                    if (hours == comp_hours && minutes == comp_minutes)
                        return ""
                    ret_str = twoDigit(hours) + ":" + twoDigit(minutes);
                    return ret_str;
                }

                if(diff >= -1 && diff <= 1)
                {
                    ret_str = get_date_time_label(ret_str);
                    return ret_str;
                }

                ret_str = twoDigit(month) + "/" + twoDigit(date) + " " + twoDigit(hours) + ":" + twoDigit(minutes);
                return ret_str;
            }

            return get_date_time_label(ret_str);
        };

        return {
            date_label: get_date_label,
            min_date_label: get_min_date_label,
            date_time_label: get_date_time_label,
            time_label: get_time_label,
            times_label: get_times_label,
            hours_label: get_hours_label,
            is_past: check_is_past,
            ellipsis_time_str: get_ellipsis_time_str,
            std_date_string: std_date_string,
            std_date_time_string: std_date_time_string
        };
    }
]);