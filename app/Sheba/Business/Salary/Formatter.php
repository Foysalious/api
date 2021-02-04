<?php namespace App\Sheba\Business\Salary;


class Formatter
{
    private $payrollSetting;
    private $salary;
    private $grossSalaryBreakdown = [];

    public function setPayrollSetting($payroll_setting)
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
        $this->grossSalaryBreakdown['salary_breakdown']['gross_salary'] = floatval($gross_salary);
        foreach ($this->payrollSetting->components as $components)
        {
            $percentage = json_decode($components->setting, 1)['percentage'];
            $this->grossSalaryBreakdown['salary_breakdown'][$components->name] = ($gross_salary * $percentage) / 100;
        }

        return $this->grossSalaryBreakdown;
    }
}
