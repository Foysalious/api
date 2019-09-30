<?php namespace Sheba\Bkash\Modules\Normal\Methods\Payout;


use Sheba\Bkash\Modules\BkashToken;

class PayoutToken extends BkashToken
{
    const REDIS_KEY_NAME = 'BKSAH_PAYOUT_TOKEN';

    public function getRedisKeyName()
    {
        return self::REDIS_KEY_NAME;
    }
}