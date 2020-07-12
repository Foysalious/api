<?php namespace Sheba\Apple;

use Illuminate\Support\Facades\Redis;

class AuthorizationCode
{
    const KEY = 'apple_auth_code_';

    public function save($code, $data)
    {
        $key = self::KEY . $code;
        Redis::set($key, json_encode($data));
        Redis::expire($key, 60 * 5);
    }

    public function get($code)
    {
        $data = Redis::get(self::KEY . $code);
        if (!$data) return null;
        return json_decode($data);
    }
}