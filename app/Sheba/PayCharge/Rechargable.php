<?php

namespace App\Sheba\PayCharge;


interface Rechargable
{
    public function rechargeWallet($amount, $data);
}