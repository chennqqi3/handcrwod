angular.module('app.filters', [])

.filter('date_label',
    function($dateutil) {
        return function(input) {
            return $dateutil.date_label(input);
        };
    }
)
.filter('min_date_label', 
    function($dateutil) {
        return function(input) {
            return $dateutil.min_date_label(input);
        };
    }
)
.filter('times_label', 
    function($dateutil) {
        return function(input) {
            return $dateutil.times_label(input);
        };
    }
)
.filter('hours_label', 
    function($dateutil) {
        return function(input) {
            return $dateutil.hours_label(input);
        };
    }
)
.filter('date_time_label', 
    function($dateutil) {
        return function(input) {
            return $dateutil.date_time_label(input);
        };
    }
)
.filter('time_label', 
    function($dateutil) {
        return function(input) {
            return $dateutil.time_label(input);
        };
    }
)
.filter('abbr', 
    function($rootScope) {
        return function(input) {
            if (input != undefined) {
                input = input + "";
                abbr = input.substr(0, 1);
                if (abbr >= 'A' && abbr <= 'Z' || abbr >= 'a' && abbr <= 'z' || abbr >= '0' && abbr <= '9')
                    abbr = input.substr(0, 2);
                return abbr;
            }
            else
                return '';
        }
    }
);