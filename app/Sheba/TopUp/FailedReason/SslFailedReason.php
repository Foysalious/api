<?php namespace Sheba\TopUp\FailedReason;

use Sheba\TopUp\ResponseCode\SslRechargeResponseCodes;

class SslFailedReason extends FailedReason
{
    public function getReason()
    {
        $recharge_response_codes = SslRechargeResponseCodes::messages();
        return $recharge_response_codes[json_decode($this->transaction)->recharge_status];
    }
}