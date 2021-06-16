<?php namespace App\Sheba\Business\PayrollComponent\Components;

use App\Models\BusinessMember;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\Dal\PayrollComponent\Type;

class GrossSalaryBreakdownCalculate
{
    private $componentPercentage;
    private $totalAmountPerComponent;
    private $grossSalaryBreakdownWithTotalAmount;

    public function __construct()
    {
        $this->componentPercentage = new GrossSalaryComponent();
        $this->totalAmountPerComponent = new GrossSalaryComponent();
        $this->grossSalaryBreakdownWithTotalAmount = [];
    }

    /**
     * @param $payroll_setting
     * @param $business_member
     * @return mixed
     */
    public function componentPercentageBreakdown($payroll_setting, $business_member)
    {
        $gross_components = $this->getBusinessMemberGrossComponent($payroll_setting, $business_member);
        $salary = $business_member->salary ? $business_member->salary->gross_salary : 0;
        $data = [];
        $total_percentage = 0;
        foreach ($gross_components as $payroll_component) {
            $percentage = floatValFormat(json_decode($payroll_component->setting, 1)['percentage']);
            $total_percentage += $percentage;
            array_push($data, [
                'id' => $payroll_component->id,
                'payroll_setting_id' => $payroll_component->payroll_setting_id,
                'name' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : $payroll_component->value,
                'percentage' => $percentage,
                'amount' => $this->percentageToAmountCalculation($salary, $percentage),
                'type' => $payroll_component->type,
                'is_default' => $payroll_component->is_default,
                'is_active' => $payroll_component->is_default ? 1 : $payroll_component->is_active,
                'is_taxable' => $payroll_component->is_taxable,
                'is_overwritten' => $payroll_component->target_id == $business_member->id ? 1 : 0
            ]);
        }

        $final_data['breakdown'] = $data;
        $final_data['total_percentage'] = $total_percentage;

        return $final_data;
    }

    /**
     * @param BusinessMember $business_member
     * @return array
     */
    public function payslipComponentPercentageBreakdown(BusinessMember $business_member)
    {
        $payroll_setting = $business_member->business->payrollSetting;
        $gross_components = $this->getBusinessMemberGrossComponent($payroll_setting, $business_member);
        $data = [];
        foreach ($gross_components as $payroll_component) {
            $percentage = floatValFormat(json_decode($payroll_component->setting, 1)['percentage']);
            $data[$payroll_component->name] = $percentage;
        }
        return $data;
    }


    public function totalAmountPerComponent($gross_salary = 0.0, $gross_salary_breakdown_percentage)
    {
        $data = ['gross_salary' => $gross_salary];
        foreach ($gross_salary_breakdown_percentage as $breakdown_name => $breakdown_value) {
            $data[$breakdown_name] = floatValFormat(($gross_salary * $breakdown_value) / 100);
        }
        return $data;
    }

    public function makeGrossComponentCollection($payroll_components, $payroll_component_by_target)
    {
        foreach ($payroll_component_by_target as $target){
            $payroll_components->search(function($value, $key) use($target, $payroll_components){
                if($value->name == $target->name) return $payroll_components->forget($key);
            });
        }
        return $payroll_components->merge($payroll_component_by_target);
    }

    private function percentageToAmountCalculation($gross_salary, $percentage)
    {
        return floatValFormat(($gross_salary * $percentage) / 100);
    }

    private function getBusinessMemberGrossComponent($payroll_setting, $business_member)
    {
        $payroll_components = $payroll_setting->components()->where('type', Type::GROSS)->where('is_active', 1)->where('target_type', TargetType::GENERAL)->orderBy('name')->get();
        $gross_components = $payroll_components;
        $payroll_component_by_target = $payroll_setting->components()->where('type', Type::GROSS)->where('target_id', $business_member->id)->where('is_active', 1)->orderBy('name')->get();
        if ($payroll_component_by_target) $gross_components = $this->makeGrossComponentCollection($payroll_components, $payroll_component_by_target);

        return $gross_components;
    }
}
