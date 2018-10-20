<?php

namespace Sheba\Payment\Adapters\Payable;


use App\Models\Payable;

interface PayableAdapter
{
    public function getPayable(): Payable;
}