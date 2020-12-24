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
    protected $proof_of_photograph;
    protected $licence_agreement_checked;
    protected $ipdc_data_agreement_checked;
    protected $ipdc_cib_agreement_checked;
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
