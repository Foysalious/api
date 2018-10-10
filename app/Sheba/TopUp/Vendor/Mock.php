<?php

namespace Sheba\TopUp\Vendor;


class Mock extends Vendor
{
    public function recharge($mobile_number, $amount, $type):  TopUpResponse
    {
        $topup_response = new TopUpSuccessResponse();
        $topup_response->transactionId = str_random(10);
        $topup_response->transactionDetails = 'Mock';
        return $topup_response;
    }
}