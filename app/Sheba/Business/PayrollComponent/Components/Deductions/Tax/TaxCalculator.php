<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions\Tax;


class TaxCalculator
{
    const BASIC_SALARY = 'basic_salary';
    const HOUSE_RENT = 'house_rent';
    const MEDICAL_ALLOWANCE = 'medical_allowance';
    const CONVEYANCE = 'conveyance';

    private $businessMember;
    private $grosSalary;
    private $grossSalaryBreakdown;
    private $taxableComponent;
    private $yearlyAmount = [];
    private $taxableIncome = 0;
    private $medicalAllowanceTaxExemptionAmount;
    private $houseRentTaxExemptionAmount;


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
        $this->calculateTaxForGrossComponents();
        $this->calculateTaxForPayrollComponents();
    }

    private function calculateTaxForGrossComponents()
    {
        foreach ($this->grossSalaryBreakdown as $gross_breakdown){
            if ($gross_breakdown['name'] == self::BASIC_SALARY) {
                $yearly_basic_salary = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $yearly_basic_salary;
                $this->houseRentTaxExemptionAmount = $yearly_basic_salary / 2;
                $this->medicalAllowanceTaxExemptionAmount = $yearly_basic_salary / 10;
                $this->taxableIncome += $yearly_basic_salary;
                continue;
            }
            if ($gross_breakdown['name'] == self::HOUSE_RENT) {
                $yearly_house_rent = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $yearly_house_rent;
                $this->taxableIncome += $yearly_house_rent - min(300000, $this->houseRentTaxExemptionAmount) <= 0 ? 0 : $yearly_house_rent - min(300000, $this->houseRentTaxExemptionAmount);
                continue;
            }
            if ($gross_breakdown['name'] == self::CONVEYANCE) {
                $yearly_conveyance = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $yearly_conveyance;
                $this->taxableIncome += $yearly_conveyance > 300000 ? $yearly_conveyance - 300000 : 0;
                continue;
            }
            if ($gross_breakdown['name'] == self::MEDICAL_ALLOWANCE) {
                $yearly_medical_allowance = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $yearly_medical_allowance;
                $this->taxableIncome += $yearly_medical_allowance - min(120000, $this->medicalAllowanceTaxExemptionAmount) <= 0 ? 0 : $yearly_medical_allowance - min(120000, $this->medicalAllowanceTaxExemptionAmount);
                continue;
            }
            if($gross_breakdown['is_taxable']) {
                $custom_gross_component = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $custom_gross_component;
                $this->taxableIncome += $custom_gross_component;
            }
        }
        //dd($this->yearlyAmount, $this->taxableIncome);
    }

    private function calculateTaxForPayrollComponents()
    {
        foreach ($this->taxableComponent as $component_packages){
            foreach ($component_packages as $package){
                //$component_packages = $component->componentPackages;
            }
        }
    }

    private function yearlyTotalGrossAmount($percentage)
    {
        return (($percentage * $this->grosSalary) / 100) * 12;
    }

}