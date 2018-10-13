<?php


namespace Sheba\PayCharge\Adapters\Error;


use Sheba\PayCharge\Methods\PayChargeMethodError;

interface MethodErrorAdapter
{
    public function getError(): PayChargeMethodError;
}