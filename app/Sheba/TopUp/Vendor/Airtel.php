<?php namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\Vendor\Internal\RobiAxiata;

class Airtel extends Vendor
{
    use RobiAxiata;

    private function getMid()
    {
        return config('topup.robi.airtel_mid');
    }

    private function getPin()
    {
        return config('topup.robi.airtel_pin');
    }
}