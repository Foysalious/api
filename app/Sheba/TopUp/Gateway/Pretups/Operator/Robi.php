<?php namespace Sheba\TopUp\Gateway\Pretups\Operator;


use Sheba\TopUp\Gateway\Pretups\Pretups;

class Robi extends Pretups
{
    use RobiAxiata;

    protected function getMid()
    {
        return config('topup.robi.robi_mid');
    }

    protected function getPin()
    {
        return config('topup.robi.robi_pin');
    }
}