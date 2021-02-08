<?php namespace App\Sheba\Business\Salary;


use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Dal\PayrollComponent\Type;

class Formatter
{
    private $payrollSetting;
    private $salary;
    private $grossSalaryBreakdown = [];

    public function setPayrollSetting(PayrollSetting $payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }

    public function setSalary($salary)
    {
        $this->salary = $salary;
        return $this;
    }

    public function calculate()
    {
        $gross_salary = $this->salary->gross_salary;
        $gross_type_components = $this->payrollSetting->components()->where('type', Type::GROSS);
        $this->grossSalaryBreakdown['salary_breakdown']['gross_salary'] = floatval($gross_salary);
        foreach ($gross_type_components as $components)
        {
            $percentage = json_decode($components->setting, 1)['percentage'];
            $this->grossSalaryBreakdown['salary_breakdown'][$components->name] = ($gross_salary * $percentage) / 100;
        }

        return $this->grossSalaryBreakdown;
    }
}
