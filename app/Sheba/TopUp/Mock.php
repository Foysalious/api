<?php

namespace Sheba\TopUp;


class Mock implements Operator
{

    public function recharge($mobile_number, $amount, $type)
    {
        return true;
    }

    public function getVendor()
    {
        return \App\Models\TopUpVendor::find(TopUpVendor::$MOCK);
    }
}