<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions\Tax;


class TaxCalculator
{
    private $businessMember;
    private $grosSalary;
    private $grossSalaryBreakdown;
    private $taxableComponent;

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setGrossSalary($gross_salary)
    {
        $this->grosSalary = $gross_salary;
        return $this;
    }

    public function setGrossSalaryBreakdown($gross_salary_breakdown)
    {
        $this->grossSalaryBreakdown = $gross_salary_breakdown;
        return $this;
    }

    public function setTaxableComponent($taxable_component)
    {
        $this->taxableComponent = $taxable_component;
        return $this;
    }

    public function calculate()
    {
        
    }

}