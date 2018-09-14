<?php


namespace Sheba\PayCharge\Adapters\Error;


use Sheba\PayCharge\Methods\MethodError;

interface MethodErrorAdapter
{
    public function getError(): MethodError;
}