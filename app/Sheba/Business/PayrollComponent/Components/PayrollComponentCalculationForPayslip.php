<?php namespace App\Sheba\Business\PayrollComponent\Components;

use App\Models\Business;
use Sheba\Dal\PayrollComponent\Type;

class PayrollComponentCalculationForPayslip
{
    private $business;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }
    public function getPayrollComponentCalculationBreakdown()
    {
        $addition = $this->getAdditionComponent();
        $deduction = $this->getDeductionComponent();

        return ['payroll_component' => array_merge($addition, $deduction)];
    }

    private function getAdditionComponent()
    {
        $components = $this->business->payrollSetting->components->where('type', Type::ADDITION)->sortBy('name');
        foreach ($components as $component) {
            $data['addition'][$component->name] = 0;
        }
        return $data;
    }

    private function getDeductionComponent()
    {
        $components = $this->business->payrollSetting->components->where('type', Type::DEDUCTION)->sortBy('name');
        foreach ($components as $component) {
            $data['deduction'][$component->name] = 0;
        }
        return $data;
    }
}