<?php namespace App\Sheba\Business\PayrollComponent\Components;

use Sheba\Dal\PayrollComponent\PayrollComponent;

abstract class GrossSalary
{
    protected $payrollComponent;

    /**
     * BasicSalary constructor.
     * @param PayrollComponent $payroll_component
     */
    public function __construct(PayrollComponent $payroll_component)
    {
        $this->payrollComponent = $payroll_component;
    }

    public abstract function getPercentage();
}