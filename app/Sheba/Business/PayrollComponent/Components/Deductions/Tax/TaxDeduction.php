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
    private $slabAmount;
    private $netTaxableIncome;

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->gender = $this->businessMember->profile()->gender;
        return $this;
    }

    public function setTaxableIncome($taxable_income)
    {
        $this->taxableIncome = $taxable_income;
        return $this;
    }

    public function calculate()
    {
        $this->netTaxableIncome = $this->getNetTaxableIncome($this->taxableIncome, $this->gender);
        $tax_slab = new TaxSlabCalculator($this->netTaxableIncome);
        $yearly_tax = $tax_slab->calculate();
        $this->slabAmount = $tax_slab->getSlabAmount();
        return $yearly_tax;
    }

    public function getSlabAmount()
    {
        return $this->slabAmount;
    }

    public function getGenderExemption()
    {
        return $this->getGenderExemptionAmount($this->gender);
    }

    public function getNetTaxableIncomeAmount()
    {
        return $this->netTaxableIncome;
    }

}