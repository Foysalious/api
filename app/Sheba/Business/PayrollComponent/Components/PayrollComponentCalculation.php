<?php namespace App\Sheba\Business\PayrollComponent\Components;

class PayrollComponentCalculation
{
    private $payrollSetting;
    /**
     * @var mixed
     */
    private $deduction;
    /**
     * @var mixed
     */
    private $addition;

    public function setPayrollSetting($payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }

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

    /**
     * @return array
     */
    private function getAdditionComponent()
    {
        foreach ($this->addition as $component => $value) {
            $data['addition'][$component] = $value;
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getDeductionComponent()
    {
        foreach ($this->deduction as $component => $value) {
            $data['deduction'][$component] = $value;
        }

        return $data;
    }

}
