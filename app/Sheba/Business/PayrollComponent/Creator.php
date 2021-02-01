<?php namespace Sheba\Business\PayrollComponent;

use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequest;

class Creator
{
    /**
     * @var PayrollSetting
     */
    private $payrollSetting;
    private $payrollComponentRepository;
    private $payrollComponentRequester;


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
        $this->payrollComponentRepository->create($this->payComponentData());
    }

    private function payComponentData()
    {
        return [
            'payroll_setting_id' => $this->payrollSetting->id,
            'name' => $this->payrollComponentRequester->getName(),
            'setting' => $this->payrollComponentRequester->setting(),
        ];
    }
}