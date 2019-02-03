<?php

use Carbon\Carbon;
use Sheba\Reward\ActionRewardDispatcher;

if (!function_exists('setTrace')) {

    /**
     * Debug data in formatted presentation.
     *
     * @param $data
     * @param bool $die
     * @return array
     */
    function setTrace($data, $die = true)
    {
        echo "<hr><pre>";
        print_r($data);
        echo "</pre><hr>";

        if ($die) exit;
    }
}

if (!function_exists('constants')) {
    /**
     * Get the constant from config constants file.
     *
     * @param String $key
     * @return mixed
     */
    function constants($key)
    {
        return config('constants.' . $key);
    }
}

if (!function_exists('formatTaka')) {
    /**
     * Format integer amount of taka into decimal.
     *
     * @param  $amount
     * @return number
     */
    function formatTaka($amount)
    {
        return number_format($amount, 2, '.', '');
    }
}

if (!function_exists('formatTaka')) {
    /**
     * Format integer amount of taka into decimal.
     *
     * @param  $amount
     * @return number
     */
    function formatTaka($amount)
    {
        return number_format($amount, 2, '.', '');
    }
}

if (!function_exists('randomString')) {
    function randomString($len, $num = 0, $alpha = 0, $spec_char = 0)
    {
        $alphabets = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $numbers = "0123456789";
        $special_characters = "!@#$%^&*()_-+=}{][|:;.,/?";
        $characters = "";
        if ($num) $characters .= $numbers;
        if ($alpha) $characters .= $alphabets;
        if ($spec_char) $characters .= $special_characters;
        if (!$num && !$alpha && !$spec_char) $characters .= $numbers . $alphabets . $special_characters;
        $rand_string = '';
        for ($i = 0; $i < $len; $i++) {
            $rand_string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $rand_string;
    }
}

if (!function_exists('formatMobileAux')) {
    /**
     * Format mobile number, add +88 & rebove space.
     * This function should be removed at refactoring.
     *
     * @param $mobile
     * @return mixed
     */
    function formatMobileAux($mobile)
    {
        $mobile = str_replace(" ", "", $mobile);
        $mobile = str_replace("-", "", $mobile);
        if ($mobile[0] == "0") {
            $mobile = "+88" . $mobile;
        }
        return $mobile;
    }
}

if (!function_exists('formatMobile')) {
    /**
     * Format Mobile number with +88 .
     *
     * @param  $number
     * @return string
     */
    function formatMobile($number)
    {
        $number = str_replace(" ", "", $number);
        $number = str_replace("-", "", $number);
        // mobile starts with '+88'
        if (preg_match("/^(\+88)/", $number)) {
            return $number;
        } // when mobile starts with '88' replace it with '+880'
        elseif (preg_match("/^(88)/", $number)) {
            return preg_replace('/^88/', '+88', $number);
        } // real mobile no add '+880' at the start
        else {
            return '+88' . $number;
        }
    }
}

if (!function_exists('getOriginalMobileNumber')) {
    /**
     * Format Mobile number with +88 .
     *
     * @param  $number
     * @return string
     */
    function getOriginalMobileNumber($number)
    {
        $number = str_replace(" ", "", $number);
        $number = str_replace("-", "", $number);
        // mobile starts with '+88'
        if (preg_match("/^(\+88)/", $number)) {
            return substr($number, 3);
        } // when mobile starts with '88' replace it with '+880'
        elseif (preg_match("/^(88)/", $number)) {
            return substr($number, 2);
        } // real mobile no add '+880' at the start
        else {
            return $number;
        }
    }
}

if (!function_exists('isEmailValid')) {
    /**
     * Email formatting check.
     *
     * @param  $email
     * @return bool
     */
    function isEmailValid($email)
    {
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        return preg_match($regex, $email);
    }
}

if (!function_exists('api_response')) {

    function api_response($request, $internal_response, $response_code, array $external_response = null)
    {
        $public_response = constants('API_RESPONSE_CODES')[$response_code];
        if ($external_response != null) {
            $public_response = array_merge($public_response, $external_response);
        }
        if (class_basename($request) == 'Request' || $request instanceof \App\Http\Requests\ApiRequest) {
            return response()->json($public_response);
        } else {
            return $internal_response;
        }
    }
}

if (!function_exists('calculatePagination')) {

    function calculatePagination($request)
    {
        $offset = $request->has('offset') ? $request->offset : 0;
        $limit = $request->has('limit') ? $request->limit : 50;
        return array($offset, $limit);
    }
}

if (!function_exists('calculatePaginationNew')) {

    function calculatePaginationNew($request)
    {
        $page = $request->has('page') ? $request->page : 0;
        $limit = $request->has('limit') ? $request->limit : 50;
        return array($page, $limit);
    }
}

if (!function_exists('calculateSort')) {

    function calculateSort($request, $default = 'id')
    {
        $offset = $request->has('sort') ? $request->sort : $default;
        $limit = $request->has('sort_order') ? $request->sort_order : 'DESC';
        return array($offset, $limit);
    }
}

if (!function_exists('getRangeFormat')) {
    function getRangeFormat($request, $param = 'range')
    {
        $filter = $request->{$param};
        $today = Carbon::today();
        $dateFrame = new \Sheba\Helpers\TimeFrame();
        switch ($filter) {
            case 'today':
                return $dateFrame->forToday()->getArray();
            case 'yesterday':
                return $dateFrame->forYesterday()->getArray();
            case 'year':
                return $dateFrame->forAYear($today->year)->getArray();
            case 'month':
                return $dateFrame->forAMonth($today->month, $today->year)->getArray();
            case 'week':
                return $dateFrame->forAWeek($today)->getArray();
            default:
                return [$today->startOfDay(), $today->endOfDay()];
        }
    }
}

if (!function_exists('createAuthorWithType')) {
    function createAuthorWithType($author)
    {
        $data = createAuthor($author);
        $data['created_by_type'] = "App\Models\\" . class_basename($author);
        return $data;
    }
}

if (!function_exists('createAuthor')) {
    function createAuthor($author)
    {
        $data = [];
        $data['created_by'] = $author->id;
        $data['created_by_name'] = class_basename($author) . " - " . ($author->profile != null ? $author->profile->name : $author->name);
        return $data;
    }
}

if (!function_exists('updateAuthor')) {
    function updateAuthor($model, $author)
    {
        $model->updated_by = $author->id;
        $model->updated_by_name = class_basename($author) . " - " . ($author->profile != null ? $author->profile->name : $author->name);
        return $model;
    }
}

if (!function_exists('removeRelationsFromModel')) {

    function removeRelationsFromModel($model)
    {
        foreach ($model->getRelations() as $key => $relation) {
            array_forget($model, $key);
        }
    }
}

if (!function_exists('removeRelationsAndFields')) {
    function removeRelationsAndFields($model, array $columns_to_remove = [])
    {
        removeRelationsFromModel($model);
        $model = removeSelectedFieldsFromModel($model, $columns_to_remove);
        return $model;
    }
}

if (!function_exists('removeSelectedFieldsFromModel')) {

    function removeSelectedFieldsFromModel($model, array $columns_to_remove = [])
    {
        array_forget($model, 'created_by');
        array_forget($model, 'updated_by');
        array_forget($model, 'updated_at');
        array_forget($model, 'created_by_name');
        array_forget($model, 'updated_by_name');
        array_forget($model, 'remember_token');
        foreach ($columns_to_remove as $column) {
            array_forget($model, $column);
        }
        return $model;
    }
}

if (!function_exists('createAuthor')) {
    function createAuthor($model, $author)
    {
        $model->created_by = $author->id;
        $model->created_by_name = class_basename($author) . " - " . ($author->profile != null ? $author->profile->name : $author->name);
        return $model;
    }
}

if (!function_exists('updateAuthor')) {
    function updateAuthor($model, $author)
    {
        $model->updated_by = $author->id;
        $model->updated_by_name = class_basename($author) . " - " . ($author->profile != null ? $author->profile->name : $author->name);
        return $model;
    }
}

if (!function_exists('getValidationErrorMessage')) {
    function getValidationErrorMessage($errors)
    {
        $msg = '';
        foreach ($errors as $error) {
            $msg .= $error;
        }
        return $msg;
    }
}

if (!function_exists('floatValFormat')) {
    function floatValFormat($value)
    {
        return floatval(number_format($value, 2, '.', ''));
    }
}

if (!function_exists('humanReadableShebaTime')) {
    function humanReadableShebaTime($time)
    {
        if ($time === 'Anytime') {
            return $time;
        }
        $time = explode('-', $time);
        return (Carbon::parse($time[0]))->format('g:i A') . (isset($time[1]) ? ('-' . (Carbon::parse($time[1]))->format('g:i A')) : '');
    }
}

if (!function_exists('clean')) {
    function clean($string, $separator = "-", $keep = [])
    {
        $string = str_replace(' ', $separator, $string); // Replaces all spaces with hyphens.
        $keep_only = "/[^A-Za-z0-9";
        foreach ($keep as $item) {
            $keep_only .= "$item";
        }
        $keep_only .= (($separator == '-') ? '\-' : "_");
        $keep_only .= "]/";

        $string = preg_replace($keep_only, '', $string); // Removes special chars.
        return preg_replace("/$separator+/", $separator, $string); // Replaces multiple hyphens with single one.
    }
}

if (!function_exists('ordinal')) {
    /**
     * Ordinal numbers refer to a position in a series.
     *
     * @param $number = any natural number
     * @return String
     */
    function ordinal($number)
    {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) return $number . 'th';
        else return $number . $ends[$number % 10];
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
            $start_time = \Carbon\Carbon::now()->year($year)->month(1)->day(1)->hour(0)->minute(0)->second(0);
            $end_time = \Carbon\Carbon::now()->year($year)->month(12)->day(31)->hour(23)->minute(59)->second(59);
            return ['start_time' => $start_time, 'end_time' => $end_time, 'days_in_month' => 31];
        } else {
            if (empty($month)) $month = \Carbon\Carbon::now()->month;
            if (empty($year)) $year = \Carbon\Carbon::now()->year;
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $start_time = \Carbon\Carbon::now()->year($year)->month($month)->day(1)->hour(0)->minute(0)->second(0);
            $end_time = \Carbon\Carbon::now()->year($year)->month($month)->day($days_in_month)->hour(23)->minute(59)->second(59);
            return ['start_time' => $start_time, 'end_time' => $end_time, 'days_in_month' => $days_in_month];
        }
    }
}

if (!function_exists('en2bnNumber')) {
    /**
     * @param  $number
     * @return string
     */
    function en2bnNumber($number)
    {
        $search_array = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
        $replace_array = ["১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০"];

        return str_replace($search_array, $replace_array, $number);
    }
}

if (!function_exists('indexedArrayToAssociativ')) {
    /**
     * @param $key
     * @param $value
     * @return array
     */
    function indexedArrayToAssociative($key, $value)
    {
        return array_combine(array_values($key), array_values($value));
    }
}

if (!function_exists('dispatchReward')) {
    function dispatchReward()
    {
        return app(ActionRewardDispatcher::class);
    }
}

if (!function_exists('getSalesChannels')) {
    /**
     * Return Sales channel associative column (default to name).
     *
     * @param $key = The result column
     * @return Array
     */
    function getSalesChannels($key = 'name')
    {
        return array_combine(array_keys(constants('SALES_CHANNELS')), array_column(constants('SALES_CHANNELS'), $key));
    }
}

if (!function_exists('formatDateRange')) {
    /**
     * Return Date Range Formatted
     *
     * @param $filter_type = Filter type to filter date range
     * @return Array
     */
    function formatDateRange($filter_type)
    {
        $currentDate = Carbon::now();

        switch ($filter_type) {
            case "today":
                return ["from" => Carbon::today(), "to" => Carbon::today()];
            case "yesterday":
                return ["from" => Carbon::yesterday()->addDay(-1), "to" => Carbon::today()];
            case "week":
                return ["from" => $currentDate->startOfWeek()->addDays(-1), "to" => Carbon::today()];
            case "month":
                return ["from" => $currentDate->startOfMonth(), "to" => Carbon::today()];
            case "year":
                return ["from" => $currentDate->startOfYear(), "to" => Carbon::today()];
            case "all_time":
                return ["from" => '2017-01-01', "to" => Carbon::today()];
            default:
                return ["from" => '2017-01-01', "to" => Carbon::today()];
        }
    }
}

if (!function_exists('createOptionsFromOptionVariables')) {

    function createOptionsFromOptionVariables($variables)
    {
        $options = '';
        foreach ($variables->options as $key => $option) {
            $input = explode(',', $option->answers);
            $output = implode(',', array_map(
                function ($value, $key) {
                    return sprintf("%s", $key);
                }, $input, array_keys($input)
            ));
            $output = '[' . $output . '],';
            $options .= $output;
        }
        return '[' . substr($options, 0, -1) . ']';
    }
}

if (!function_exists('trim_phone_number')) {
    function trim_phone_number($number, $index_number = '0')
    {
        return strstr($number, $index_number);
    }
}

if (!function_exists('pamel_case')) {
    function pamel_case($string)
    {
        return ucfirst(camel_case($string));
    }
}

if (!function_exists('scramble_string')) {
    /**
     * Returns scrambled string replaced by '*'
     *
     *
     * @param $scramble_ratio = The ratio (in percentage) by which the visible portion of the string is shown
     * @return String
     */
    function scramble_string($str, $scramble_ratio = 15)
    {
        $str = \Sheba\BanglaToEnglish::convert($str);
        $str=preg_replace('/[\x00-\x1F\x7F]/u', '', $str);
        $len = strlen($str);
        $number_of_words_visible = (int)ceil(($scramble_ratio * $len) / 100);
        $number_of_words_hidden = $len - ($number_of_words_visible * 2);
        return substr($str, 0, $number_of_words_visible) . str_repeat('*', $number_of_words_hidden) . substr($str, $len - $number_of_words_visible, $len);
    }
}

if (!function_exists('isResourceAdmin')) {
    /**
     * Returns true if resource is admin, else return false if handyman
     *
     *
     * @param array $resource_types
     * @return String
     */
    function isResourceAdmin($resource_types = array())
    {
        return (array_intersect($resource_types, ['Admin', 'Operation', 'Finance', 'Management', 'Owner'])) ? true : false;
    }
}

if (!function_exists('getDefaultWorkingDays')) {
    /**
     * Returns default working days of sheba
     *
     *
     * @return array
     */
    function getDefaultWorkingDays()
    {
        return ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
}

if (!function_exists('getDefaultWorkingHours')) {
    /**
     * Returns default working days of sheba
     *
     *
     * @return object
     */
    function getDefaultWorkingHours()
    {
        return (object) [
            'start_time' => '09:00:00',
            'end_time' => '18:00:00'
        ];
    }
}