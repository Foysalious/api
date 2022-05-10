<?php

namespace Sheba\Payment\Methods\Upay\Response;

use Illuminate\Contracts\Support\Arrayable;
use Sheba\Helpers\ArrayReflection;
use Sheba\Loan\DS\ReflectionArray;

class UpayLoginResponse extends ArrayReflection
{
    public $merchant_id;
    public $token;

}