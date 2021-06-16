<?php namespace App\Sheba\Business\PayrollComponent\Components;

use App\Sheba\Business\PayrollComponent\Components\GrossSalary;

class GrossComponents extends GrossSalary
{
    public function getPercentage()
    {
        $salary_percentage = json_decode($this->payrollComponent->setting, 1);
        return (int)$salary_percentage['percentage'];
    }
}
