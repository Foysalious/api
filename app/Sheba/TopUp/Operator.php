<?php

namespace Sheba\TopUp;

interface Operator
{
    public function recharge($msisdn);
}