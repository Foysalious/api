<?php

namespace Sheba\Loan\DS;

use Illuminate\Contracts\Support\Arrayable;

class SalesInfo implements Arrayable
{
    use ReflectionArray;

    protected $avg_sell;
    protected $min_sell;
    protected $max_sell;
}
