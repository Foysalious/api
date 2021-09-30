<?php namespace App\Sheba\Business\PayrollComponent\Components;

class PayrollComponentCalculation
{
    private $deduction;
    private $addition;

    public function setAddition($addition)
    {
        $this->addition = $addition;
        return $this;
    }

    public function setDeduction($deduction)
    {
        $this->deduction = $deduction;
        return $this;
    }

    public function getCalculationBreakdown()
    {
        $addition = $this->getAdditionComponent();
        $deduction = $this->getDeductionComponent();

        return ['payroll_component' => array_merge($addition, $deduction)];
    }

    private function getAdditionComponent()
    {
        $data = [];
        foreach ($this->addition as $component_name => $component_value) {
            $data['addition'][$component_name] = $component_value;
        }
        return $data;
    }

    private function getDeductionComponent()
    {
        $data = [];
        foreach ($this->deduction as $component_name => $component_value) {
            $data['deduction'][$component_name] = $component_value;
        }

        return $data;
    }
}
