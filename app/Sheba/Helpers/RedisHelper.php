<?php namespace Sheba\Helpers;

use Illuminate\Support\Facades\Redis;

class RedisHelper
{
    public static function getAllByKey($key)
    {
        return Redis::lrange($key, 0, -1);
    }

    public static function pushToKey($key, $value)
    {
        Redis::rpush($key, $value);
    }

    /**
     * REMOVE ALL OCCURRENCE OF A VALUE FROM KEY
     *
     * @param $key
     * @param $value
     */
    public static function removeAllOccurrenceOfValueFromKey($key, $value)
    {
        Redis::lrem($key, 0, $value);
    }
}