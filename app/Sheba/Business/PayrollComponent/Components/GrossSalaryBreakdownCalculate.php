<?php namespace App\Sheba\Business\PayrollComponent\Components;

use Sheba\Business\PayrollComponent\Components\MedicalAllowance;
use Sheba\Business\PayrollComponent\Components\BasicSalary;
use Sheba\Business\PayrollComponent\Components\Conveyance;
use Sheba\Business\PayrollComponent\Components\HouseRent;
use Sheba\Dal\PayrollComponent\PayrollComponent;
use Sheba\Dal\PayrollComponent\Components;
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
     * @return GrossSalaryComponent
     */
    public function componentPercentageBreakdown($payroll_setting, $business_member)
    {
        /** @var PayrollComponent $payroll_components */
        $payroll_components = $payroll_setting->components()->where('type', Type::GROSS)->get();
        $payroll_component_by_target = $payroll_setting->components()->where('type', Type::GROSS)->where('target_id', $business_member->id)->get();
        if ($payroll_component_by_target) $gross_components = $this->makeGrossComponentCollection($payroll_components, $payroll_component_by_target);
        $data = [];
        foreach ($gross_components as $payroll_component) {
            array_push($data, [
                'id' => $payroll_component->id,
                'payroll_setting_id' => $payroll_component->payroll_setting_id,
                'name' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : $payroll_component->value,
                'percentage' => json_decode($payroll_component->setting, 1)['percentage'],
                'type' => $payroll_component->type,
                'is_default' => $payroll_component->is_default,
                'is_active' => $payroll_component->is_active,
                'is_taxable' => $payroll_component->is_taxable,
                'is_overwritten' => $payroll_component->target_id == $business_member->id ? 1 : 0
            ]);
        }
        return $data;
    }


    /**
     * @param float $gross_salary
     * @return GrossSalaryComponent
     */
    public function totalAmountPerComponent($gross_salary = 0.0)
    {
        $this->totalAmountPerComponent->basicSalary = floatValFormat(($gross_salary * $this->componentPercentage->basicSalary) / 100);
        $this->totalAmountPerComponent->houseRent = floatValFormat(($gross_salary * $this->componentPercentage->houseRent) / 100);
        $this->totalAmountPerComponent->medicalAllowance = floatValFormat(($gross_salary * $this->componentPercentage->medicalAllowance) / 100);
        $this->totalAmountPerComponent->conveyance = floatValFormat(($gross_salary * $this->componentPercentage->conveyance) / 100);
        $this->totalAmountPerComponent->grossSalary = floatValFormat($gross_salary);
        return $this->totalAmountPerComponent;
    }

    /**
     * @return array[]
     */
    public function totalAmountPerComponentFormatted()
    {
        $this->grossSalaryBreakdownWithTotalAmount = [
            'gross_salary_breakdown' => [
                'basic_salary' => $this->totalAmountPerComponent->basicSalary,
                'house_rent' => $this->totalAmountPerComponent->houseRent,
                'medical_allowance' => $this->totalAmountPerComponent->medicalAllowance,
                'conveyance' => $this->totalAmountPerComponent->conveyance,
                'gross_salary' => $this->totalAmountPerComponent->grossSalary,
            ]
        ];
        return $this->grossSalaryBreakdownWithTotalAmount;
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
}
