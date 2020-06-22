<?php namespace Sheba\TopUp\Vendor;

class Robi extends Vendor
{
    private function getMid()
    {
        return config('topup.robi.robi_mid');
    }

    private function getPin()
    {
        return config('topup.robi.robi_pin');
    }
}
