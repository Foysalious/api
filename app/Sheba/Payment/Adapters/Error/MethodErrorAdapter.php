<?php


namespace Sheba\Payment\Adapters\Error;


use Sheba\Payment\Methods\PayChargeMethodError;

interface MethodErrorAdapter
{
    public function getError(): PayChargeMethodError;
}