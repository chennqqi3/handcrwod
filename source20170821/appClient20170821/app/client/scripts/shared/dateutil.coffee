angular.module('app.dateutil', [])

.factory('$dateutil', 
    ($session, numberFilter, $numutil) ->
        get_date_label = (date) ->
            return '' if date == null or date == undefined
            dt = moment.tz(date, $session.time_zone)
            now = moment.tz($session.time_zone) 
            sod = now.startOf('day')
            diff = dt.diff(sod, 'days', true)
            if diff < -6 
                format = 'sameElse'
            else if diff < -1
                format = 'lastWeek'
            else if diff < 0
                format = 'lastDay'
            else if diff < 1
                format = 'sameDay'
            else if diff < 2
                format = 'nextDay'
            else if diff < 7
                format = 'nextWeek'
            else 
                format = 'sameElse'
            format = dt.localeData().calendar(format, dt, moment(now))
            format = format.replace /\ LT$/, ''
            d = dt.format(format)
            if d == 'Invalid date'
                d = ''
            return dt.format(format)

        get_min_date_label = (date) ->
            return '' if date == null or date == undefined
            dt = moment.tz(date, $session.time_zone)
            now = moment.tz($session.time_zone) 
            sod = now.startOf('day')
            diff = dt.diff(sod, 'days', true)
            if diff < -6 
                format = 'L'
            else if diff < -1
                format = 'lastWeek'
            else if diff < 0
                format = 'lastDay'
            else if diff < 1
                format = 'sameDay'
            else if diff < 2
                format = 'nextDay'
            else if diff < 7
                format = 'nextWeek'
            else 
                format = 'L'
            if format != "L"
                format = dt.localeData().calendar(format, dt, moment(now))
                format = format.replace /\ LT$/, ''
            d = dt.format(format)
            if d == 'Invalid date'
                d = ''
            return dt.format(format)
        
         get_date_time_label = (date) ->
            return '' if date == null or date == undefined
            date = date + ''
            date = date.replace(/\ /gi, 'T')
            dt = moment.tz(date, $session.time_zone)
            now = moment.tz($session.time_zone) 
            sod = now.startOf('day')
            diff = dt.diff(sod, 'days', true)
            if diff < -6 
                format = 'sameElse'
            else if diff < -1
                format = 'lastWeek'
            else if diff < 0
                format = 'lastDay'
            else if diff < 1
                format = 'sameDay'
            else if diff < 2
                format = 'nextDay'
            else if diff < 7
                format = 'nextWeek'
            else 
                format = 'sameElse'
            format = dt.localeData().calendar(format, dt, moment(now))
            format = format.replace /\ LT$/, ''
            format = format + " LT"
            lbl = dt.format(format)
            return lbl

        check_is_past = (date) ->
            return false if date == null or date == undefined
            return moment.tz(date, $session.time_zone).isBefore(moment())

        get_times_label = (hours) ->
            return '' if hours == null or hours == undefined or hours == NaN
            day = hours // 8
            hour = $numutil.to_decimal(hours - day * 8, 2)
            return hour + '時間' if day == 0
            return numberFilter(day) + '人日' if hour == 0
            return numberFilter(day) + '人日' + hour + '時間'

        get_hours_label = (hours) ->
            return '' if hours == null or hours == undefined or hours == NaN
            return numberFilter(hours) + '時間'

        std_date_string = (date) ->
            return date.getFullYear() + "-" + twoDigit(date.getMonth() + 1) + "-" + twoDigit(date.getDate())

        twoDigit = (val) ->
            if !isNaN(val) && val.toString().length==1
                return "0" + val
            else
                return val

        get_ellipsis_time_str = (date_time_str, compare_date_time_str) ->
            ret_str = date_time_str
            if (compare_date_time_str == null) || (compare_date_time_str == "")
                return get_date_time_label(ret_str)

            time = moment.tz(date_time_str, $session.time_zone)
            year = time.year()
            month = time.month() + 1
            date = time.date()
            hours = time.hours()
            minutes = time.minutes()

            comp_time = moment.tz(compare_date_time_str, $session.time_zone)
            comp_year = comp_time.year()
            comp_month = comp_time.month() + 1
            comp_date = comp_time.date()
            comp_hours = comp_time.hours()
            comp_minutes = comp_time.minutes()

            now = moment()
            sod = now.startOf("day")
            diff = time.diff(sod, "days", true)

            if year == comp_year
                if (month == comp_month) && (date == comp_date)
                    if (hours == comp_hours) && (minutes == comp_minutes)
                        return ""
                    ret_str = twoDigit(hours) + ":" + twoDigit(minutes)
                    return ret_str
                if diff >= -1 and diff <= 1
                    ret_str = get_date_time_label(ret_str)
                    return ret_str
                ret_str = twoDigit(month) + "/" + twoDigit(date) + " " + twoDigit(hours) + ":" + twoDigit(minutes)
                return ret_str

            return get_date_time_label(ret_str)

        return {
            date_label: get_date_label
            min_date_label: get_min_date_label
            date_time_label: get_date_time_label
            times_label: get_times_label
            hours_label: get_hours_label
            is_past: check_is_past
            ellipsis_time_str: get_ellipsis_time_str            
            std_date_string: std_date_string
        }
)