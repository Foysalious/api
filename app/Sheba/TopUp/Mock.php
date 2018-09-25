<?php

namespace Sheba\TopUp;


class Mock implements Operator
{

    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
        $topup_response = new TopUpResponse();
        $topup_response->transactionId = str_random(10);
        $topup_response->transactionDetails = 'Mock';
        return $topup_response;
    }

    public function getVendor()
    {
        return \App\Models\TopUpVendor::find(TopUpVendor::$MOCK);
    }
}