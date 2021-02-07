<?php namespace Sheba\Business\PayrollComponent\Components;

use App\Sheba\Business\PayrollComponent\Components\GrossSalary;

class BasicSalary extends GrossSalary
{
    public function getPercentage()
    {
        $salary_percentage = json_decode($this->payrollComponent->setting, 1);
        return $salary_percentage['percentage'];
    }
}