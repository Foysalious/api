<?php namespace Sheba\Business\PayrollComponent;

use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequest;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Dal\PayrollComponent\Components;

class Creator
{
    /**
     * @var PayrollSetting
     */
    private $payrollSetting;
    private $payrollComponentRepository;
    private $payrollComponentRequester;
    private $payrollComponentData = [];


    public function __construct(PayrollComponentRepository $payroll_component_repository)
    {
        $this->payrollComponentRepository = $payroll_component_repository;
    }

    public function setPayrollSetting(PayrollSetting $payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }

    public function setPayrollComponentRequester(PayrollComponentRequest $payroll_component_requester)
    {
        $this->payrollComponentRequester = $payroll_component_requester;
        return $this;
    }

    public function create()
    {
        $this->payrollComponentData();
        $this->payrollComponentRepository->insert($this->payrollComponentData);
    }

    private function payrollComponentData()
    {
        foreach (Components::get() as $key => $component) {
            $this->payrollComponentData[] = [
                'payroll_setting_id' => $this->payrollSetting->id,
                'name' => $component,
                'setting' => json_encode(['percentage' => 0]),
            ];
        }
        return $this->payrollComponentData;
    }
}