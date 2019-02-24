<?php namespace Sheba\Helpers;

use ReflectionClass;

trait ConstGetter
{
    public static function getWithKeys()
    {
        $class = new ReflectionClass(__CLASS__);
        return $class->getConstants();
    }

    public static function get()
    {
        return array_values(self::getWithKeys());
    }

    public static function getWithMadeKeys()
    {
        $result = [];
        foreach (self::get() as $item) {
            $result[$item] = antiCases($item);
        }
        return $result;
    }
}