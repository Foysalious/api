<?php

namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\Vendor\Internal\Rax;

class Robi extends Vendor
{
    use Rax;

    private function setup()
    {
        $this->mid = config('topup.robi.robi_mid');
        $this->pin = config('topup.robi.robi_pin');
    }
}