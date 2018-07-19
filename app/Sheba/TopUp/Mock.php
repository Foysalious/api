<?php

namespace Sheba\TopUp;


class Mock implements Operator
{

    public function recharge($mobile_number)
    {
        return true;
    }
}