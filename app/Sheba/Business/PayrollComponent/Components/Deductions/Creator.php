<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions;

use Illuminate\Support\Facades\DB;
use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequester;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\TargetType;

class Creator
{
    /*** @var PayrollComponentRepository */
    private $payrollComponentRepository;
    /**
     * @var PayrollComponentRequester
     */
    private $payrollComponentRequester;
    private $payrollComponentData = [];

    public function __construct(PayrollComponentRepository $payroll_component_repository)
    {
        $this->payrollComponentRepository = $payroll_component_repository;
    }

    public function setPayrollComponentRequester(PayrollComponentRequester $payroll_component_requester)
    {
        $this->payrollComponentRequester = $payroll_component_requester;
        return $this;
    }

    public function createOrUpdate()
    {
        DB::transaction(function () {
            $this->makeData();
            if ($this->payrollComponentData)
                $this->payrollComponentRepository->insert($this->payrollComponentData);
        });
    }

    public function delete()
    {
        $deduction_component_delete = $this->payrollComponentRequester->getDeductionComponentDelete();
        if (!$deduction_component_delete) return;
        foreach ($deduction_component_delete as $component) {
            $existing_component = $this->payrollComponentRepository->find($component);
            if (!$existing_component) continue;
            $this->payrollComponentRepository->delete($existing_component);
        }
        return true;
    }

    private function makeData()
    {
        $payroll_settings = $this->payrollComponentRequester->getSetting();
        $this->addData($payroll_settings);
        $this->updateData($payroll_settings);
    }

    private function addData($payroll_settings)
    {
        $add_deduction_components = $this->payrollComponentRequester->getAddDeductionComponent();
        if ($add_deduction_components)
            foreach ($add_deduction_components as $component) {
                $this->payrollComponentData[] = [
                    'payroll_setting_id' => $payroll_settings->id,
                    'name' => $component['name'],
                    'value' => $component['title'],
                    'type' => 'deduction',
                    'target_type' => TargetType::GENERAL,
                    'is_default' => 0,
                    'is_active' => 1,
                    'setting' => json_encode([]),
                ];
            }
    }

    private function updateData($payroll_settings)
    {
        $update_deduction_components = $this->payrollComponentRequester->getUpdateDeductionComponent();
        if ($update_deduction_components)
            foreach ($update_deduction_components as $component) {
                $data = [
                    'payroll_setting_id' => $payroll_settings->id,
                    'name' => $component['name'],
                    'value' => $component['title'],
                    'type' => 'deduction',
                    'is_default' => 0,
                    'is_active' => 1,
                    'setting' => json_encode([]),
                ];
                $existing_component = $this->payrollComponentRepository->find($component['id']);
                $this->payrollComponentRepository->update($existing_component, $data);
            }
    }
}
