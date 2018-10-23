<?php

namespace App\Sheba\Payment;


interface Rechargable
{
    public function rechargeWallet($amount, $data);
}