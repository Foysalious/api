<?php namespace App\Sheba\Business\PayrollComponent\Components;

use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\PayrollComponent;
use Sheba\Dal\PayrollComponent\Type;

class GrossSalaryBreakdownCalculate
{
    private $componentPercentage = [];
    private $totalAmountPerComponent;
    private $grossSalaryBreakdownWithTotalAmount;

    public function __construct()
    {
        $this->grossSalaryBreakdownWithTotalAmount = [];
    }

    /**
     * @param $payroll_setting
     * @return GrossSalaryComponent
     */
    public function componentPercentageBreakdown($payroll_setting)
    {
        /** @var PayrollComponent $payroll_components */
        $payroll_components = $payroll_setting->components()->where('type', Type::GROSS)->get();
        foreach ($payroll_components as $payroll_component) {
             array_push($this->componentPercentage, [
                 'id' => $payroll_component->id,
                'key' => $payroll_component->name,
                'title' => Components::getComponents($payroll_component->name)['value'],
                'is_default' => $payroll_component->is_default,
                'value' =>(new GrossComponents($payroll_component))->getPercentage(),
                'is_enable' => 1,
                'is_taxable' => 0,
            ]);
        }
        return $this->componentPercentage;
    }

    public function totalPercentage($payroll_setting)
    {
        $payroll_components = $payroll_setting->components()->where('type', Type::GROSS)->get();
        $total = 0;
        foreach ($payroll_components as $payroll_component) {
            $total += (new GrossComponents($payroll_component))->getPercentage();
        }

        return $total;
    }


    /**
     * @param float $gross_salary
     * @return GrossSalaryComponent
     */
    public function totalAmountPerComponent($gross_salary = 0.0)
    {
        $this->totalAmountPerComponent->basicSalary = floatValFormat(($gross_salary * $this->componentPercentage->basicSalary) / 100);
        $this->totalAmountPerComponent->houseRent = floatValFormat(($gross_salary * $this->componentPercentage->houseRent) / 100);
        $this->totalAmountPerComponent->medicalAllowance = floatValFormat(($gross_salary * $this->componentPercentage->medicalAllowance) / 100);
        $this->totalAmountPerComponent->conveyance = floatValFormat(($gross_salary * $this->componentPercentage->conveyance) / 100);
        $this->totalAmountPerComponent->grossSalary = floatValFormat($gross_salary);
        return $this->totalAmountPerComponent;
    }

    /**
     * @return array[]
     */
    public function totalAmountPerComponentFormatted()
    {
        $this->grossSalaryBreakdownWithTotalAmount = [
            'gross_salary_breakdown' => [
                'basic_salary' => $this->totalAmountPerComponent->basicSalary,
                'house_rent' => $this->totalAmountPerComponent->houseRent,
                'medical_allowance' => $this->totalAmountPerComponent->medicalAllowance,
                'conveyance' => $this->totalAmountPerComponent->conveyance,
                'gross_salary' => $this->totalAmountPerComponent->grossSalary,
            ]
        ];
        return $this->grossSalaryBreakdownWithTotalAmount;
    }
}
