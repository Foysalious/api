<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions\Tax;


use App\Models\BusinessMember;
use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;

class TaxDeduction
{
    use PayrollCommonCalculation;
    /*** @var BusinessMember */
    private $businessMember;
    private $taxableIncome;
    private $gender;

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->gender = $this->businessMember->member->profile->gender;
        return $this;
    }

    public function setTaxableIncome($taxable_income)
    {
        $this->taxableIncome = $taxable_income;
        return $this;
    }

    public function calculate()
    {
        $net_taxable_income = $this->getNetTaxableIncome($this->taxableIncome, $this->gender);
        $tax_slab = new TaxSlabCalculator($net_taxable_income);
        return $tax_slab->calculate();
    }

}