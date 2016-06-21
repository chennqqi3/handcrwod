'use strict'

angular.module('app.filters', [])

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
                return 'ホームなし'
            else
                return input
)
.filter('abbr', 
    ($rootScope) ->
        return (input) ->
            abbr = input.substr(0, 1)
            if abbr >= 'A' && abbr <= 'Z' || abbr >= 'a' && abbr <= 'z' || abbr >= '0' && abbr <= '9' 
                abbr = input.substr(0, 2)
            return abbr
)
