<?php namespace Sheba\TopUp\Gateway\Pretups\Operator;


use Sheba\TopUp\Gateway\Pretups\Pretups;

class Airtel extends Pretups
{
    use RobiAxiata;

    protected function getMid()
    {
        return config('topup.robi.airtel_mid');
    }

    protected function getPin()
    {
        return config('topup.robi.airtel_pin');
    }
}