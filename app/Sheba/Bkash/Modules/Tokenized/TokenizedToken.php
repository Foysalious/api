<?php

namespace Sheba\Bkash\Modules\Tokenized;


use Sheba\Bkash\Modules\BkashToken;

class TokenizedToken extends BkashToken
{
    const REDIS_KEY_NAME = 'TOKENIZED_BKAH_TOKEN';


    public function getRedisKeyName()
    {
        return self::REDIS_KEY_NAME;
    }
}