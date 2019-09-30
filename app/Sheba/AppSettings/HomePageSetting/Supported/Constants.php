<?php namespace Sheba\AppSettings\HomePageSetting\Supported;

use Sheba\Helpers\ConstGetter;

abstract class Constants
{
    use ConstGetter;
    
    public static function isSupported($value)
    {
        return in_array($value, self::get());
    }

    public static function validate($value)
    {
        if(!self::isSupported($value)) {
            static::throwException($value);
        }
    }

    /**
     * @param $value
     */
    abstract protected static function throwException($value);
}