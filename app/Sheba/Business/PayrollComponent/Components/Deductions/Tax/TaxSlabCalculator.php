<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions\Tax;


use App\Sheba\Business\PayrollSetting\PayrollConstGetter;

class TaxSlabCalculator
{
    private $netTaxableIncome;

    public function __construct($net_taxable_income)
    {
        $this->netTaxableIncome = $net_taxable_income;
    }

    public function calculate()
    {
        $first_slab = $this->netTaxableIncome > PayrollConstGetter::FIRST_TAX_SLAB ? PayrollConstGetter::FIRST_TAX_SLAB : $this->netTaxableIncome;
        $first_slab_tax_amount = ($first_slab * PayrollConstGetter::FIRST_TAX_SLAB_PERCENTAGE) / 100;

        $second_slab = $this->netTaxableIncome > PayrollConstGetter::FIRST_TAX_SLAB  ? $this->netTaxableIncome > (PayrollConstGetter::FIRST_TAX_SLAB +  PayrollConstGetter::SECOND_TAX_SLAB) ? PayrollConstGetter::SECOND_TAX_SLAB : ($this->netTaxableIncome - PayrollConstGetter::FIRST_TAX_SLAB) : 0;
        $second_slab_tax_amount = ($second_slab * PayrollConstGetter::SECOND_TAX_SLAB_PERCENTAGE) / 100;

        $third_slab = $this->netTaxableIncome > (PayrollConstGetter::FIRST_TAX_SLAB + PayrollConstGetter::SECOND_TAX_SLAB) ? $this->netTaxableIncome > (PayrollConstGetter::FIRST_TAX_SLAB +  PayrollConstGetter::SECOND_TAX_SLAB + PayrollConstGetter::THIRD_TAX_SLAB) ? PayrollConstGetter::THIRD_TAX_SLAB : ($this->netTaxableIncome - PayrollConstGetter::FIRST_TAX_SLAB - PayrollConstGetter::SECOND_TAX_SLAB ) : 0;
        $third_slab_tax_amount = ($third_slab * PayrollConstGetter::THIRD_TAX_SLAB_PERCENTAGE) / 100;

        $fourth_slab = $this->netTaxableIncome > (PayrollConstGetter::FIRST_TAX_SLAB + PayrollConstGetter::SECOND_TAX_SLAB + PayrollConstGetter::THIRD_TAX_SLAB) ? $this->netTaxableIncome > (PayrollConstGetter::FIRST_TAX_SLAB +  PayrollConstGetter::SECOND_TAX_SLAB + PayrollConstGetter::THIRD_TAX_SLAB + PayrollConstGetter::FOURTH_TAX_SLAB) ? PayrollConstGetter::FOURTH_TAX_SLAB : ($this->netTaxableIncome - PayrollConstGetter::FIRST_TAX_SLAB - PayrollConstGetter::SECOND_TAX_SLAB - PayrollConstGetter::THIRD_TAX_SLAB) : 0;
        $fourth_slab_tax_amount = ($fourth_slab * PayrollConstGetter::FOURTH_TAX_SLAB_PERCENTAGE) / 100;

        $fifth_slab = $this->netTaxableIncome > (PayrollConstGetter::FIRST_TAX_SLAB + PayrollConstGetter::SECOND_TAX_SLAB + PayrollConstGetter::THIRD_TAX_SLAB + PayrollConstGetter::FOURTH_TAX_SLAB) ? $this->netTaxableIncome - (PayrollConstGetter::FIRST_TAX_SLAB +  PayrollConstGetter::SECOND_TAX_SLAB + PayrollConstGetter::THIRD_TAX_SLAB + PayrollConstGetter::FOURTH_TAX_SLAB) : 0;
        $fifth_slab_tax_amount = ($fifth_slab * PayrollConstGetter::FIFTH_TAX_SLAB_PERCENTAGE) / 100;

        //dd($first_slab_tax_amount, $second_slab_tax_amount, $third_slab_tax_amount, $fourth_slab_tax_amount, $fifth_slab_tax_amount);

        return ($first_slab_tax_amount + $second_slab_tax_amount + $third_slab_tax_amount + $fourth_slab_tax_amount + $fifth_slab_tax_amount);
    }
}