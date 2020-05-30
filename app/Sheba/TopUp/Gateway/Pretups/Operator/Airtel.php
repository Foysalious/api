<?php namespace Sheba\TopUp\Gateway\Pretups\Operator;

use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Gateway\Pretups\Pretups;

class Airtel extends Pretups implements Gateway
{
    use RobiAxiata;
    CONST SHEBA_COMMISSION = 3.60;

    protected function getMid()
    {
        return config('topup.robi.airtel_mid');
    }

    protected function getPin()
    {
        return config('topup.robi.airtel_pin');
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getBalance()
    {
        // TODO: Implement getBalance() method.
    }
}
