<?php

use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\Helpers\Formatters\TakaFormatter;

if (!function_exists('formatMobile')) {
    /**
     * Format Mobile number with +88 .
     *
     * 01. Mobile starts with '+88'
     * 02. When mobile starts with '88' replace it with '+88'
     * 03. Real mobile no add '+880' at the start
     *
     * @param  $number
     * @return string
     */
    function formatMobile($number)
    {
        return BDMobileFormatter::format($number);
    }
}

if (!function_exists('commaSeparate')) {
    /**
     * Format comma separated number.
     *
     * @param  $amount
     * @param  $decimal
     * @param  $format
     * @return number
     */
    function commaSeparate($amount, $decimal = 0, $format = "BDT")
    {
        return TakaFormatter::commaSeparate($amount, $decimal, $format);
    }
}

if (!function_exists('formatTaka')) {
    /**
     * Format integer amount of taka into decimal.
     *
     * @param  $amount
     * @param  $comma_separation
     * @param  $comma_separation_format
     * @return number
     */
    function formatTaka($amount, $comma_separation = false, $comma_separation_format = "BDT")
    {
        return TakaFormatter::formatTaka($amount, $comma_separation, $comma_separation_format);
    }
}

if (!function_exists('formatMobileAux')) {
    /**
     * Format mobile number, add +88 & remove space.
     * This function should be removed at refactoring.
     *
     * @param $mobile
     * @return mixed
     */
    function formatMobileAux($mobile)
    {
        return BDMobileFormatter::formatAux($mobile);
    }
}

if (!function_exists('formatMobileReverse')) {
    /**
     * Format mobile number, remove +88.
     *
     * @param $mobile
     * @return mixed
     */
    function formatMobileReverse($mobile)
    {
        return BDMobileFormatter::reverse($mobile);
    }
}

if (!function_exists('currencyShortenFormat')) {
    /**
     * Return shorthand currency format.
     *
     * @param $amount
     * @param $precision = 1
     * @return string
     */
    function currencyShortenFormat($amount, $precision = 1)
    {
        return TakaFormatter::currencyShortenFormat($amount, $precision);
    }
}