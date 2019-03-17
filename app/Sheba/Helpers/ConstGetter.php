<?php namespace Sheba\Helpers;

use ReflectionClass;

trait ConstGetter
{
    public static function getWithKeys()
    {
        $class = new ReflectionClass(static::class);
        return $class->getConstants();
    }

    public static function get()
    {
        return array_values(static::getWithKeys());
    }

    public static function getWithMadeKeys()
    {
        $result = [];
        foreach (static::get() as $item) {
            $result[$item] = normalizeCases($item);
        }
        return $result;
    }
}