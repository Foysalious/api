<?php

namespace Sheba\TopUp;


class Robi implements Operator
{
    public function getVendor()
    {
        return \App\Models\TopUpVendor::find(TopUpVendor::$ROBI);
    }

    public function recharge($mobile_number, $amount, $type)
    {

    }
}