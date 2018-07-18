<?php

use Carbon\Carbon;

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

        if ($die)
            exit;
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
        if ($num)
            $characters .= $numbers;
        if ($alpha)
            $characters .= $alphabets;
        if ($spec_char)
            $characters .= $special_characters;
        if (!$num && !$alpha && !$spec_char)
            $characters .= $numbers . $alphabets . $special_characters;
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
        if (class_basename($request) == 'Request') {
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

if (!function_exists('removeSelectedFieldsFromModel')) {

    function removeSelectedFieldsFromModel($model)
    {
        array_forget($model, 'created_by');
        array_forget($model, 'updated_by');
        array_forget($model, 'updated_at');
        array_forget($model, 'created_by_name');
        array_forget($model, 'updated_by_name');
        array_forget($model, 'remember_token');
    }
}

if (!function_exists('removeRelationsAndFields')) {
    function removeRelationsAndFields($model)
    {
        removeRelationsFromModel($model);
        removeSelectedFieldsFromModel($model);
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
        return (Carbon::parse($time[0]))->format('g:i A') . '-' . (Carbon::parse($time[1]))->format('g:i A');
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
        if ((($number % 100) >= 11) && (($number % 100) <= 13))
            return $number . 'th';
        else
            return $number . $ends[$number % 10];
    }
}