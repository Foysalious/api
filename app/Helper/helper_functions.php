<?php

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

        if($die)
            exit;
    }
}

if(!function_exists('clean')) {
    /**
     * Clean a string from all special characters.
     *
     * @param String $string
     * @return App\Models\Partner
     */
    function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }
}

if(!function_exists('constants')) {
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