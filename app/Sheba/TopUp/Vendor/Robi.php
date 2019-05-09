<?php namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\Vendor\Internal\RobiAxiata;

class Robi extends Vendor
{
    use RobiAxiata;

    private function getMid()
    {
        return config('topup.robi.robi_mid');
    }

    private function getPin()
    {
        return config('topup.robi.robi_mid');
    }
}