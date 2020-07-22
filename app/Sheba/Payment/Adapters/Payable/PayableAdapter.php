<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;

interface PayableAdapter
{
    public function getPayable(): Payable;

    public function setModelForPayable($model);

    public function setEmiMonth($month);

    public function canInit(): bool;
}