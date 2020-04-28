<?php namespace Sheba\Helpers;

use ReflectionClass;

trait ConstGetter
{
    public static function getAllWithout(...$excludes)
    {
        if (is_array($excludes[0])) $excludes = $excludes[0];
        return array_diff(static::get(), $excludes);
    }

    public static function get()
    {
        return array_values(static::getWithKeys());
    }

    public static function getWithKeys()
    {
        $class = new ReflectionClass(static::class);
        return $class->getConstants();
    }

    public static function getWithMadeKeys()
    {
        $result = [];
        foreach (static::get() as $item) {
            $result[$item] = normalizeStringCases($item);
        }
        return $result;
    }
}
