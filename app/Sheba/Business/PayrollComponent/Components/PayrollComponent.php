<?php namespace App\Sheba\Business\PayrollComponent\Components;

class PayrollComponent
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

    public function getBreakdown()
    {
        $addition = $this->getAdditionComponent();
        $deduction = $this->getDeductionComponent();

        return ['payroll_component' => array_merge($addition, $deduction)];
    }

    private function getAdditionComponent()
    {
        foreach ($this->addition as $key => $value) {
            $data['addition'][$key] = $value;
        }

        return $data;
    }

    private function getDeductionComponent()
    {
        foreach ($this->deduction as $key => $value) {
            $data['deduction'][$key] = $value;
        }

        return $data;
    }

}
