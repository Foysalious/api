<?php

namespace Sheba\TopUp;


interface TopUpAgent
{
    public function doRecharge($vendor_id, $mobile_number, $amount, $type);

    public function topUpTransaction($amount, $log);

    public function refund($amount, $log);
}