<?php namespace Sheba\Subscription\Partner\Access;


class AccessManager
{
    /**
     * @param $feature
     * @param $access_rules
     * @return bool
     */
    public static function canAccess($feature, $access_rules)
    {
        $feature = explode('.', $feature);
        return self::get_value($feature, $access_rules);
    }

    public static function get_value($access_key, $access_rules)
    {
        if (count($access_key) > 1) return self::get_value(array_slice($access_key, 1), $access_rules[$access_key[0]]);
        else return $access_rules[$access_key[0]];
    }

    public static function Rules(): Rules
    {
        return new Rules();
    }
}
