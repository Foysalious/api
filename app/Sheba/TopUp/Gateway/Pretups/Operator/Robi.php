<?php namespace Sheba\TopUp\Gateway\Pretups\Operator;

use Sheba\TopUp\Gateway\FailedReason;
use Sheba\TopUp\Gateway\FailedReason\RobiFailedReason;
use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Gateway\Names;
use Sheba\TopUp\Gateway\Pretups\Pretups;

class Robi extends Pretups implements Gateway
{
    use RobiAxiata;

    CONST SHEBA_COMMISSION = 4.02;

    protected function getMid()
    {
        return config('topup.robi.robi_mid');
    }

    protected function getPin()
    {
        return config('topup.robi.robi_pin');
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName()
    {
        return Names::ROBI;
    }

    public function getFailedReason(): FailedReason
    {
        return new RobiFailedReason();
    }
}
