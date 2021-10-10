<?php

use Carbon\Carbon;
use Sheba\Reward\ActionRewardDispatcher;

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

if (!function_exists('indexedArrayToAssociative')) {
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
    /**
     * @return ActionRewardDispatcher
     */
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
     * @return array
     */
    function getSalesChannels($key = 'name')
    {
        return array_combine(array_keys(constants('SALES_CHANNELS')), array_column(constants('SALES_CHANNELS'), $key));
    }
}

if (!function_exists('createOptionsFromOptionVariables')) {
    /**
     * @param $variables
     * @return string
     */
    function createOptionsFromOptionVariables($variables)
    {
        $options = '';
        foreach ($variables->options as $key => $option) {
            $input   = explode(',', $option->answers);
            $output  = implode(',', array_map(function ($value, $key) {
                return sprintf("%s", $key);
            }, $input, array_keys($input)));
            $output  = '[' . $output . '],';
            $options .= $output;
        }
        return '[' . substr($options, 0, -1) . ']';
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
        return (array_intersect($resource_types, [
            'Admin',
            'Operation',
            'Finance',
            'Management',
            'Owner'
        ])) ? true : false;
    }
}

if (!function_exists('ramp')) {
    /**
     * @param $value
     * @return string
     */
    function ramp($value)
    {
        return max($value, 0);
    }
}

if (!function_exists('isAssoc')) {
    /**
     * @param $arr
     * @return bool
     */
    function isAssoc($arr)
    {
        if (!is_array($arr)) return false;
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

if (!function_exists('asset_from')) {
    /**
     * Generate an asset path for the application.
     * Might be a missing laravel helper.
     *
     * @param string $root
     * @param string $path
     * @param bool $secure
     * @return string
     */
    function asset_from($root, $path, $secure = null)
    {
        return app('url')->assetFrom($root, $path, $secure);
    }
}

if (!function_exists('getCDNAssetsFolder')) {
    /**
     * @return string
     */
    function getCDNAssetsFolder()
    {
        return config('s3.url');
    }
}

if (!function_exists('assetLink')) {
    /**
     * @param  $file
     * @return string
     */
    function assetLink($file)
    {
        return config('sheba.use_cdn_for_asset') ? asset_from(getCDNAssetsFolder(), $file) : asset($file);
    }
}

if (!function_exists('isInProduction')) {
    /**
     * @return bool
     */
    function isInProduction()
    {
        return app()->environment() == "production";
    }
}

if (!function_exists('getBloodGroupsList')) {

    /**
     * Get list of blood groups.
     *
     * @param bool $associate
     * @return array
     */
    function getBloodGroupsList($associate = true)
    {
        $groups = [
            'A+',
            'B+',
            'AB+',
            'O+',
            'A-',
            'B-',
            'AB-',
            'O-'
        ];
        return $associate ? array_combine($groups, $groups) : $groups;
    }
}

if (!function_exists('emi_calculator')) {
    /**
     * @param $interest
     * @param $amount
     * @param $duration
     * @return float|int
     */
    function emi_calculator($interest, $amount, $duration)
    {
        $rate     = (double)($interest / (12 * 100));
        $duration = (int)$duration;
        if (!$interest || !$amount || !$duration)
            return 0;
        $accessor = ((double)$amount * $rate * pow((1 + $rate), $duration));
        $divisor  = (pow((1 + $rate), $duration) - 1);
        return $divisor > 0 ? round($accessor / $divisor) : round($amount / $duration);
    }
}

if (!function_exists('calculateAge')) {
    /**
     * @param $dob
     * @return int
     */
    function calculateAge($dob)
    {
        if (empty($dob)) return 0;

        try {
            return Carbon::parse($dob)->age;
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('hasSameValues')) {
    /**
     * @param array $a
     * @param array $b
     * @return bool
     */
    function hasSameValues(array $a, array $b)
    {
        return array_diff($a, $b) === array_diff($b, $a);
    }
}

if (!function_exists('isStringInt')) {
    /**
     * @param string $value
     * @return bool
     */
    function isStringInt($value)
    {
        return preg_match('/^\d+$/', $value);
    }
}

if (!function_exists('simplifyExceptionTrace')) {
    /**
     * @param \Throwable $e
     * @return array
     */
    function simplifyExceptionTrace(\Throwable $e)
    {
        return collect(explode(PHP_EOL, $e->getTraceAsString()))->mapWithKeys(function ($trace) {
            $trace = explode(": ", preg_replace('/^(#\d+ )(.*)$/', '$2', $trace));
            if (count($trace) == 1) $trace[1] = "";
            return [$trace[0] => $trace[1]];
        })->all();
    }
}

if (!function_exists('dde')) {
    /**
     * @param \Throwable $e
     */
    function dde(\Throwable $e)
    {
        dd(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), simplifyExceptionTrace($e));
    }
}

if (!function_exists('array_push_on_array')) {
    /**
     * @param array $array
     * @param $key
     * @param $value
     */
    function array_push_on_array(array &$array, $key, $value)
    {
        if (!array_key_exists($key, $array)) $array[$key] = [];

        $array[$key][] = $value;
    }

    if (!function_exists('convertTimezone')) {

        /**
         * @param Carbon|null $datetime
         * @param string $timezone
         * @return Carbon|null
         */
        function convertTimezone(Carbon $datetime,  $timezone = 'Asia/Dhaka')
        {
            return $datetime->timezone($timezone);

        }
    }
}
