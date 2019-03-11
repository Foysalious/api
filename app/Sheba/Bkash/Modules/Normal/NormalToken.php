<?php namespace Sheba\Bkash\Modules\Normal;


use Sheba\Bkash\Modules\BkashToken;

class NormalToken extends BkashToken
{
    const REDIS_KEY_NAME = 'BKSAH_TOKEN';

    public function getRedisKeyName()
    {
        return self::REDIS_KEY_NAME;
    }
}