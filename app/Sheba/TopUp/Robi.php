<?php

namespace Sheba\TopUp;


class Robi extends Ssl implements Operator
{
    public function getVendor()
    {
        return \App\Models\TopUpVendor::find(TopUpVendor::$ROBI);
    }
}