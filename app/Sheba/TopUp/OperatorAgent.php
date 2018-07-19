<?php

namespace Sheba\TopUp;


interface OperatorAgent
{
    public function doRecharge($vendor_id, $mobile_number, $amount, $type);

    public function topUpTransaction($amount, $log);
}