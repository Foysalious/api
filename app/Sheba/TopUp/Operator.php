<?php

namespace Sheba\TopUp;

interface Operator
{
    public function recharge($mobile_number);
}