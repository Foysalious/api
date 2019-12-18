<?php

namespace Sheba\Loan\DS;

use Illuminate\Contracts\Support\Arrayable;

class BusinessAdditionalInfo implements Arrayable
{
    use ReflectionArray;
    protected $product_price;
    protected $employee_salary;
    protected $office_rent;
    protected $utility_bills;
    protected $marketing_cost;
    protected $other_cost;
}
