<?php namespace App\Transformers\Business;


use App\Sheba\Business\PayrollSetting\PayrollConstGetter;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\TaxHistory\TaxHistory;

class TaxHistoryListTransformer extends TransformerAbstract
{
    public function transform(TaxHistory $tax_history)
    {
        $business_member = $tax_history->businessMember;
        $department = $business_member->department();
        $exemption_amount = $tax_history->exemption_amount;
        $remaining_taxable_income = $tax_history->remaining_taxable_income;
        $components_breakdown = $this->getTaxableComponentAmount($tax_history);
        $slabs_amount = $this->getTaxSlabAmount($tax_history->slabs_amount);

        return array_merge([
            'id' =>   $tax_history->id,
            'business_member_id' => $tax_history->business_member_id,
            'employee_id' => $business_member->employee_id ? $business_member->employee_id : 'N/A',
            'employee_name' => $business_member->profile()->name,
            'department' => $department ? $department->name : 'N/A',
            'generated_at' => Carbon::parse($tax_history->generated_at)->format('Y-m-d'),
            'exemption_amount' => floatValFormat($exemption_amount),
            'remaining_taxable_income' => floatValFormat($remaining_taxable_income),
            'total_taxable_income' => floatValFormat($exemption_amount + $remaining_taxable_income),
            'total_tax_amount_yearly' => floatValFormat($tax_history->yearly_amount),
            'total_tax_amount_monthly' => floatValFormat($tax_history->monthly_amount),
        ], $components_breakdown, $slabs_amount);
    }

    private function getTaxableComponentAmount($tax_history)
    {
        $gross_components = json_decode($tax_history->gross_components, 1);
        $additional_components = json_decode($tax_history->addition_components, 1);
        $data = [];
        $others = 0;
        foreach ($gross_components as $component_name => $gross_component){
            if ($component_name === PayrollConstGetter::BASIC_SALARY) $data['basic_salary'] = $gross_component;
            else if ($component_name === PayrollConstGetter::HOUSE_RENT) $data['house_rent'] = $gross_component;
            else if ($component_name === PayrollConstGetter::MEDICAL_ALLOWANCE) $data['medical_allowance'] = $gross_component;
            else if ($component_name === PayrollConstGetter::CONVEYANCE) $data['conveyance'] = $gross_component;
            else $others += $gross_component;
        }
        foreach ($additional_components as $additional_component){
            $others += $additional_component;
        }
        $data['others'] = floatValFormat($others);

        return $data;
    }

    private function getTaxSlabAmount($slabs_amount)
    {
        $slabs_amount = json_decode($slabs_amount, 1);
        $data = [];
        foreach ($slabs_amount as $slab_portion => $slab_amount){
            if ($slab_portion === PayrollConstGetter::FIRST_TAX_SLAB_PERCENTAGE) $data['5_percent_slab'] = $slab_amount;
            if ($slab_portion === PayrollConstGetter::SECOND_TAX_SLAB_PERCENTAGE) $data['10_percent_slab'] = $slab_amount;
            if ($slab_portion === PayrollConstGetter::THIRD_TAX_SLAB_PERCENTAGE) $data['15_percent_slab'] = $slab_amount;
            if ($slab_portion === PayrollConstGetter::FOURTH_TAX_SLAB_PERCENTAGE) $data['20_percent_slab'] = $slab_amount;
            if ($slab_portion === PayrollConstGetter::FIFTH_TAX_SLAB_PERCENTAGE) $data['25_percent_slab'] = $slab_amount;
        }
        return $data;
    }

}