<?php namespace Sheba\Business\PayrollComponent\Components;

use Sheba\Dal\PayrollComponent\PayrollComponent;

class BasicSalary
{
    private $payrollComponent;

    /**
     * BasicSalary constructor.
     * @param PayrollComponent $payroll_component
     */
    public function __construct(PayrollComponent $payroll_component)
    {
        $this->payrollComponent = $payroll_component;
    }

    public function getPercentage()
    {
        $salary_percentage = json_decode($this->payrollComponent->setting, 1);
        return $salary_percentage['percentage'];
    }
}