<?php namespace Sheba\Subscription\Partner\Access;


use Sheba\Subscription\Partner\Access\Exceptions\AccessRestrictedExceptionForPackage;

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

    /**
     * @param $access_key
     * @param $access_rules
     * @return mixed
     */
    public static function get_value($access_key, $access_rules)
    {
        if (count($access_key) > 1) return self::get_value(array_slice($access_key, 1), $access_rules[$access_key[0]]);
        else return $access_rules[$access_key[0]];
    }

    /**
     * @param $feature
     * @param $access_rules
     * @throws AccessRestrictedExceptionForPackage
     */
    public static function checkAccess($feature, $access_rules){
        $can=self::canAccess($feature, $access_rules);
        if(!$can){
            throw new AccessRestrictedExceptionForPackage();
        }
    }

    /**
     * @return Rules
     */
    public static function Rules(): Rules
    {
        return new Rules();
    }
}
