<?php namespace Sheba\Business\PayrollComponent;

use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequest;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\TargetType;
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
        $this->getDefaultGrossComponents();
        $this->getDefaultAdditionComponents();
        $this->getDefaultDeductionComponents();

        return $this->payrollComponentData;
    }

    private function getDefaultGrossComponents()
    {
        foreach (Components::getDefaultComponents() as $key => $component) {
            $this->payrollComponentData[] = [
                'payroll_setting_id' => $this->payrollSetting->id,
                'name' => $component['key'],
                'type' => $component['type'],
                'is_default' => 1,
                'is_active' => 1,
                'target_type' => TargetType::GENERAL,
                'setting' => json_encode(['percentage' => 0]),
            ];
        }
        return $this->payrollComponentData;
    }

    private function getDefaultAdditionComponents()
    {
        foreach (Components::getDefaultAdditionComponentsV2() as $key => $component) {
            $this->payrollComponentData[] = [
                'payroll_setting_id' => $this->payrollSetting->id,
                'name' => $component['key'],
                'type' => $component['type'],
                'is_default' => 1,
                'is_active' => 1,
                'target_type' => TargetType::GENERAL,
                'setting' => json_encode([]),
            ];
        }
        return $this->payrollComponentData;
    }

    private function getDefaultDeductionComponents()
    {
        foreach (Components::getDefaultDeductionComponents() as $key => $component) {
            $this->payrollComponentData[] = [
                'payroll_setting_id' => $this->payrollSetting->id,
                'name' => $component['key'],
                'type' => $component['type'],
                'is_default' => 1,
                'is_active' => 1,
                'target_type' => TargetType::GENERAL,
                'setting' => json_encode([]),
            ];
        }
        return $this->payrollComponentData;
    }
}
