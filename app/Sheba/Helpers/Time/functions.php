<?php

use Carbon\Carbon;
use Sheba\Helpers\Converters\NumberLanguageConverter;
use Sheba\Helpers\Time\TimeAgoCalculator;
use Sheba\Helpers\TimeFrame;

if (!function_exists('timeAgo')) {
    /**
     * @param $time_ago
     * @return array
     */
    function timeAgo($time_ago)
    {
        return (new TimeAgoCalculator($time_ago))->get();
    }
}

if (!function_exists('getRangeFormat')) {
    /**
     * @param $request
     * @param string $param
     * @return array
     */
    function getRangeFormat($request, $param = 'range')
    {
        $filter    = is_array($request) ? $request[$param] : $request->{$param};
        $today     = Carbon::today();
        $date_frame = new TimeFrame();
        switch ($filter) {
            case 'today':
                return $date_frame->forToday()->getArray();
            case 'yesterday':
                return $date_frame->forYesterday()->getArray();
            case 'year':
                return $date_frame->forAYear($today->year)->getArray();
            case 'last_year':
                return $date_frame->forAYear($today->year - 1)->getArray();
            case 'month':
                return $date_frame->forAMonth($today->month, $today->year)->getArray();
            case 'last_month':
                return $date_frame->forLastMonth($today)->getArray();
            case 'week':
                return $date_frame->forAWeek($today)->getArray();
            case 'last_week':
                return $date_frame->forLastWeek($today)->getArray();
            case 'quarter':
                return $date_frame->forAQuarter($today)->getArray();
            case 'last_quarter':
                return $date_frame->forAQuarter($today, true)->getArray();
            case 'lifetime':
                return $date_frame->forLifeTime()->getArray();
            default:
                return $date_frame->forToday()->getArray();
        }
    }
}

if (!function_exists('getDayName')) {
    /**
     * @param Carbon $date
     * @return int|string
     */
    function getDayName(Carbon $date)
    {
        switch (1) {
            case $date->isToday():
                return "today";
            case $date->isTomorrow():
                return 'tomorrow';
            case $date->isPast():
                return $date->isYesterday() ? "yesterday" : Carbon::now()->diffInDays($date);
            default:
                return $date->format('M-j, Y');
        }
    }
}

if (!function_exists('getDayNameAndDateTime')) {
    /**
     * @param Carbon $date
     * @return int|string
     */
    function getDayNameAndDateTime(Carbon $date)
    {
        switch (1) {
            case $date->isToday():
                return Carbon::now()->format('h:iA');
            case $date->isYesterday():
                return 'Yesterday at ' . $date->format('h:iA');
            default:
                return $date->format('d M') . ' at ' . $date->format('h:iA');
        }
    }
}

if (!function_exists('humanReadableShebaTime')) {
    /**
     * @param $time
     * @return array|string
     */
    function humanReadableShebaTime($time)
    {
        if ($time === 'Anytime') return $time;
        $time = explode('-', $time);
        return (Carbon::parse($time[0]))->format('g:i A') . (isset($time[1]) ? ('-' . (Carbon::parse($time[1]))->format('g:i A')) : '');
    }
}

if (!function_exists('findStartEndDateOfAMonth')) {
    /**
     * @param $month
     * @param $year
     * @return array
     */
    function findStartEndDateOfAMonth($month = null, $year = null)
    {
        if ($month == 0 && $year != 0) {
            $start_time = Carbon::now()->year($year)->month(1)->day(1)->hour(0)->minute(0)->second(0);
            $end_time   = Carbon::now()->year($year)->month(12)->day(31)->hour(23)->minute(59)->second(59);
            return [
                'start_time'    => $start_time,
                'end_time'      => $end_time,
                'days_in_month' => 31
            ];
        }

        if (empty($month)) $month = Carbon::now()->month;
        if (empty($year)) $year = Carbon::now()->year;
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start_time    = Carbon::create($year, $month);
        $end_time      = Carbon::create($year, $month, $days_in_month, 23, 59, 59);
        return [
            'start_time'    => $start_time,
            'end_time'      => $end_time,
            'days_in_month' => $days_in_month
        ];
    }
}

if (!function_exists('formatDateRange')) {
    /**
     * Return Date Range Formatted
     *
     * @param $filter_type = Filter type to filter date range
     * @return array
     */
    function formatDateRange($filter_type)
    {
        $current_date = Carbon::now();
        switch ($filter_type) {
            case "today":
                return [
                    "from" => Carbon::today(),
                    "to"   => Carbon::today()
                ];
            case "yesterday":
                return [
                    "from" => Carbon::yesterday()->addDay(-1),
                    "to"   => Carbon::today()
                ];
            case "week":
                return [
                    "from" => $current_date->startOfWeek()->addDays(-1),
                    "to"   => Carbon::today()
                ];
            case "month":
                return [
                    "from" => $current_date->startOfMonth(),
                    "to"   => Carbon::today()
                ];
            case "year":
                return [
                    "from" => $current_date->startOfYear(),
                    "to"   => Carbon::today()
                ];
            case "all_time":
                return [
                    "from" => '2017-01-01',
                    "to"   => Carbon::today()
                ];
            default:
                return [
                    "from" => '2017-01-01',
                    "to"   => Carbon::today()
                ];
        }
    }
}

if (!function_exists('getDefaultWorkingDays')) {
    /**
     * Returns default working days of sheba
     *
     * @return array
     */
    function getDefaultWorkingDays()
    {
        return [
            'Saturday',
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday'
        ];
    }
}

if (!function_exists('getDefaultWorkingHours')) {
    /**
     * Returns default working days of sheba
     *
     * @return object
     */
    function getDefaultWorkingHours()
    {
        return (object)[
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00'
        ];
    }
}

if (!function_exists('getMonthsName')) {
    /**
     * Return months array.
     *
     * @param string $format
     * @return array
     */
    function getMonthsName($format = "m")
    {
        $months = [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        ];

        if ($format == "M") return $months;

        return array_map(function($s) { return substr($s, 3); }, $months);
    }
}

if (!function_exists('getMonthsNameInBangla')) {
    /**
     * Return months array.
     *
     * @return array
     */
    function getMonthsNameInBangla()
    {
        return [
            '???????????????????????????',
            '?????????????????????????????????',
            '???????????????',
            '??????????????????',
            '??????',
            '?????????',
            '???????????????',
            '???????????????',
            '??????????????????????????????',
            '?????????????????????',
            '?????????????????????',
            '????????????????????????'
        ];
    }
}

if (!function_exists('getMonthName')) {
    /**
     * Return months array.
     *
     * @param int $month_no
     * @param string $format
     * @return array
     */
    function getMonthName($month_no, $format = "m")
    {
        return getMonthsName($format)[$month_no - 1];
    }
}

if (!function_exists('getPrettifyTimeDifference')) {
    /**
     * Return months array.
     * @param Carbon $deferrable_timer
     * @param string $language
     * @return int
     */
    function getTimeDifference(Carbon $deferrable_timer, $language = 'en')
    {
        $diff_in_seconds = Carbon::now()->diffInSeconds($deferrable_timer);
        $diff_in_minutes = Carbon::now()->diffInMinutes($deferrable_timer);
        $diff_in_hours   = Carbon::now()->diffInHours($deferrable_timer);
        $diff_in_days    = Carbon::now()->diffInDays($deferrable_timer);
        $is_in_english   = $language == 'en';
        if ($diff_in_seconds < 60)
            return $is_in_english ? $diff_in_seconds . ' Second' : en2bnNumber($diff_in_seconds) . ' ?????????????????????';
        if ($diff_in_minutes < 60)
            return $is_in_english ? $diff_in_minutes . ' Minute' : en2bnNumber($diff_in_minutes) . ' ???????????????';
        if ($diff_in_hours < 24)
            return $is_in_english ? $diff_in_hours . ' Hour' : en2bnNumber($diff_in_hours) . ' ???????????????';
        return $is_in_english ? $diff_in_days . ' Day' : en2bnNumber($diff_in_days) . ' ?????????';
    }
}

if (!function_exists('banglaMonth')) {
    /**
     * @param int $month
     * @return mixed
     */
    function banglaMonth(int $month)
    {
        if ($month > 0) $month -= 1;

        return getMonthsNameInBangla()[$month];
    }
}

if (!function_exists('dayTimeConvertBn')) {
    /**
     * @param $time
     * @return string
     */
    function dayTimeConvertBn($time): string
    {
        $time = date_create($time);
        $hour = date_format($time,"H");
        if($hour >= 0 && $hour <= 5)
            $dayOfTime = "?????????";
        else if($hour > 5 && $hour < 12)
            $dayOfTime = "????????????";
        else if($hour >= 12 && $hour <= 15)
            $dayOfTime = "???????????????";
        else if($hour >= 16 && $hour <= 17)
            $dayOfTime = "???????????????";
        else if($hour >= 18 && $hour <= 19)
            $dayOfTime = "?????????????????????";
        else if($hour >= 20 && $hour <= 24)
            $dayOfTime = "?????????";

        return $dayOfTime." ".NumberLanguageConverter::en2bn(date_format($time,"h")).':'.NumberLanguageConverter::en2bn((date_format($time,"i")));
    }
}

if (!function_exists('dateRangeTitleBn')) {
    /**
     * @param string $title
     * @param array $date_range
     * @param string $date_separator
     * @return string
     */
    function dateRangeTitleBn(string $title, array $date_range, string $date_separator='/'): string
    {
        switch ($title) {
            case 'today':
                return sprintf('?????? (%s)',
                    convertNumbersToBangla($date_range[0]->day, false) . $date_separator.
                    convertNumbersToBangla($date_range[0]->month, false) . $date_separator .
                    convertNumbersToBangla($date_range[0]->year, false));
            case 'yesterday':
                return sprintf('?????? ????????? (%s)',
                    convertNumbersToBangla($date_range[0]->day, false) . $date_separator.
                    convertNumbersToBangla($date_range[0]->month, false) . $date_separator .
                    convertNumbersToBangla($date_range[0]->year, false));
            case 'year':
                return sprintf('?????? ????????? (%s)',convertNumbersToBangla($date_range[0]->year, false));
            case 'last_year':
                return sprintf('?????? ????????? (%s)',convertNumbersToBangla($date_range[0]->year, false));
            case 'month':
                return sprintf('?????? ????????? (%s)',banglaMonth($date_range[0]->month));
            case 'last_month':
                return sprintf('?????? ????????? (%s)',banglaMonth($date_range[0]->month));
            case 'week':
                return sprintf('?????? ?????????????????? (%1$s - %2$s)',
                    convertNumbersToBangla($date_range[0]->day, false) . $date_separator.
                    convertNumbersToBangla($date_range[0]->month, false) . $date_separator .
                    convertNumbersToBangla($date_range[0]->year, false),
                    convertNumbersToBangla($date_range[1]->day, false) . $date_separator.
                    convertNumbersToBangla($date_range[1]->month, false) . $date_separator .
                    convertNumbersToBangla($date_range[1]->year, false));
            case 'last_week':
                return sprintf('?????? ?????????????????? (%1$s - %2$s)',
                    convertNumbersToBangla($date_range[0]->day, false) . $date_separator.
                    convertNumbersToBangla($date_range[0]->month, false) . $date_separator .
                    convertNumbersToBangla($date_range[0]->year, false),
                    convertNumbersToBangla($date_range[1]->day, false) . $date_separator.
                    convertNumbersToBangla($date_range[1]->month, false) . $date_separator .
                    convertNumbersToBangla($date_range[1]->year, false));
            case 'quarter':
                return sprintf('?????? ??????????????????????????? (%1$s - %2$s)', banglaMonth($date_range[0]->month),
                    banglaMonth($date_range[1]->month));
            case 'last_quarter':
                return sprintf('?????? ??????????????????????????? (%1$s - %2$s)', banglaMonth($date_range[0]->month),
                    banglaMonth($date_range[1]->month));
            default:
                return '' ;
        }
    }
}

if(!function_exists('dateEn2Bn')){
    /**
     * @param $date
     * @param string $format
     * @return array|string|string[]
     */
    function dateEn2Bn($date, string $format='d:m:y h:i:s'){
        $date = new Carbon($date);
        if(strpos(($format), 'M')){
            $format = str_replace("M","XXX",$format);
            $banglaMonth = banglaMonth($date->format('m'));
            $bnglaDate = NumberLanguageConverter::en2bn($date->format($format));
            return str_replace("XXX",$banglaMonth,$bnglaDate);
        }
        return NumberLanguageConverter::en2bn($date->format($format));
    }
}
