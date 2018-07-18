<?php

namespace Sheba\TopUp;


interface OperatorAgent
{
    public function recharge($operator_name, $mobile_number);
}