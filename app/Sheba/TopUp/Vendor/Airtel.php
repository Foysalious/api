<?php namespace Sheba\TopUp\Vendor;


class Airtel extends Vendor
{
    private function getMid()
    {
        return config('topup.robi.airtel_mid');
    }

    private function getPin()
    {
        return config('topup.robi.airtel_pin');
    }
}
