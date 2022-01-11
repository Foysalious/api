<?php namespace App\Sheba\Business\PayrollComponent\Components\GrossComponents\GrossBreakdownHistory;

class Creator
{
    private $businessMember;
    private $grossSalaryBreakdown;

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setGrossSalaryBreakdown($gross_salary)
    {
        $this->grossSalaryBreakdown = $gross_salary;
        return $this;
    }

    public function update()
    {
        
    }

}