<?php

namespace Sheba\Loan\DS;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionException;

class BusinessAdditionalInfo implements Arrayable
{
    use ReflectionArray;
    protected $product_price;
    protected $employee_salary;
    protected $office_rent;
    protected $utility_bills;
    protected $marketing_cost;
    protected $other_cost;
    protected $yearly_sales;
    protected $address;

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toVersionArray()
    {
        $oldData = $this->toArray();
        return array_merge($oldData, ['address' => (new Address($this->address))->toArray()]);
    }
}
