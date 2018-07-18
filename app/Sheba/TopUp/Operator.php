<?php

namespace Sheba\TopUp;

interface Operator
{
    public function recharge($to, $from);
}