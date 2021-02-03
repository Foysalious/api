<?php namespace Sheba\Business\PayrollComponent;

use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequest;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\PayrollSetting\PayrollSetting;

class Updater
{
    /**
     * @var PayrollSetting
     */
    private $payrollSetting;
    private $payrollComponentRepository;
    private $payrollComponentRequester;
    private $payrollGrossComponentData = [];
    private $grossComponents;


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

    public function setGrossComponents($gross_components)
    {
        $this->grossComponents = json_decode($gross_components, 1);
        return $this;
    }

    public function updateGrossComponents()
    {
        $this->formatGrossComponents();
        $this->payrollComponentRepository->insert($this->payrollGrossComponentData);
    }

    public function formatGrossComponents()
    {
        $this->payrollSetting->components()->where('type', Type::GROSS)->delete();
        foreach ($this->grossComponents as $key => $component) {
            $this->payrollGrossComponentData[] = [
                'payroll_setting_id' => $this->payrollSetting->id,
                'name' => $component['component'],
                'type' => $component['type'],
                'setting' => json_encode(['percentage' => $component['percentage']]),
            ];
        }
        return $this->payrollGrossComponentData;
    }
}