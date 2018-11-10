<?php

namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\Vendor\Internal\Rax;

class Airtel extends Vendor
{
    use Rax;

    private function setup()
    {
        $this->mid = config('topup.robi.airtel_mid');
        $this->pin = config('topup.robi.airtel_pin');
    }
}