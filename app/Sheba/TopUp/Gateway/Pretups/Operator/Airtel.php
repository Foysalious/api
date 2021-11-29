<?php namespace Sheba\TopUp\Gateway\Pretups\Operator;

use Sheba\TopUp\Gateway\FailedReason;
use Sheba\TopUp\Gateway\FailedReason\AirtelFailedReason;
use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Gateway\Names;
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

    public function getName()
    {
        return Names::AIRTEL;
    }

    public function getFailedReason(): FailedReason
    {
        return new AirtelFailedReason();
    }
}
