<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions\Tax;


use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use App\Sheba\Business\PayrollSetting\PayrollConstGetter;
use Sheba\Dal\PayrollComponentPackage\CalculationType;

class TaxCalculator
{
    use PayrollCommonCalculation;

    private $businessMember;
    private $grosSalary;
    private $grossSalaryBreakdown;
    private $taxableComponent;
    private $yearlyAmount = [];
    private $taxableIncome = 0;
    private $medicalAllowanceTaxExemptionAmount;
    private $houseRentTaxExemptionAmount;
    /** @var TaxDeduction $taxDeduction */
    private $taxDeduction;
    private $monthlyTaxAmount;
    private $yearlyTaxAmount;


    public function __construct()
    {
        $this->taxDeduction = app(TaxDeduction::class);
    }


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
        $this->taxableIncome = 0;
        $this->calculateTaxForGrossComponents();
        $this->calculateTaxForPayrollComponents();
        $this->yearlyTaxAmount = $this->taxDeduction->setBusinessMember($this->businessMember)->setTaxableIncome($this->taxableIncome)->calculate();
    }

    public function getMonthlyTaxAmount()
    {
        return ($this->yearlyTaxAmount / 12);
    }

    private function calculateTaxForGrossComponents()
    {
        foreach ($this->grossSalaryBreakdown as $gross_breakdown){
            if ($gross_breakdown['name'] == PayrollConstGetter::BASIC_SALARY) {
                $yearly_basic_salary = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $yearly_basic_salary;
                $this->houseRentTaxExemptionAmount = $yearly_basic_salary / 2;
                $this->medicalAllowanceTaxExemptionAmount = $yearly_basic_salary / 10;
                $this->taxableIncome += $yearly_basic_salary;
                continue;
            }
            if ($gross_breakdown['name'] == PayrollConstGetter::HOUSE_RENT) {
                $yearly_house_rent = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $yearly_house_rent;
                $this->taxableIncome += $yearly_house_rent - min(PayrollConstGetter::HOUSE_RENT_EXEMPTION, $this->houseRentTaxExemptionAmount) <= 0 ? 0 : $yearly_house_rent - min(PayrollConstGetter::HOUSE_RENT_EXEMPTION, $this->houseRentTaxExemptionAmount);
                continue;
            }
            if ($gross_breakdown['name'] == PayrollConstGetter::CONVEYANCE) {
                $yearly_conveyance = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $yearly_conveyance;
                $this->taxableIncome += $yearly_conveyance > PayrollConstGetter::CONVEYANCE_EXEMPTION ? $yearly_conveyance - PayrollConstGetter::CONVEYANCE_EXEMPTION : 0;
                continue;
            }
            if ($gross_breakdown['name'] == PayrollConstGetter::MEDICAL_ALLOWANCE) {
                $yearly_medical_allowance = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $yearly_medical_allowance;
                $this->taxableIncome += $yearly_medical_allowance - min(PayrollConstGetter::MEDICAL_ALLOWANCE_EXEMPTION, $this->medicalAllowanceTaxExemptionAmount) <= 0 ? 0 : $yearly_medical_allowance - min(PayrollConstGetter::MEDICAL_ALLOWANCE_EXEMPTION, $this->medicalAllowanceTaxExemptionAmount);
                continue;
            }
            if($gross_breakdown['is_taxable']) {
                $custom_gross_component = $this->yearlyTotalGrossAmount($gross_breakdown['percentage']);
                $this->yearlyAmount[] = $custom_gross_component;
                $this->taxableIncome += $custom_gross_component;
            }
        }
    }

    private function calculateTaxForPayrollComponents()
    {
        foreach ($this->taxableComponent as $component_packages){
            $final_amount = 0;
            foreach ($component_packages as $package){
                $calculation_type = $package->calculation_type;
                $on_what = $package->on_what;
                $amount = floatValFormat($package->amount);
                if ($calculation_type == CalculationType::VARIABLE_AMOUNT) {
                    $final_amount += 0;
                    continue;
                }
                if ($calculation_type == CalculationType::FIX_PAY_AMOUNT) {
                    $period = $package->periodic_schedule ? (12 / intval($package->periodic_schedule)) : 1;
                    $final_amount += ($this->getFixPayAmountCalculation($this->businessMember, $package, $on_what, $amount) * $period);
                }
            }
            $this->taxableIncome += $final_amount;
        }
    }

}