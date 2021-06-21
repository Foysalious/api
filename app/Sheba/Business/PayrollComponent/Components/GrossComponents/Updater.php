<?php namespace App\Sheba\Business\PayrollComponent\Components\GrossComponents;

use Illuminate\Support\Facades\DB;
use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequester;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    /** @var PayrollComponentRequester */
    private $payrollComponentRequester;
    /** * @var PayrollComponentRepository */
    private $payrollComponentRepository;

    public function __construct(PayrollComponentRepository $payroll_component_repository)
    {
        $this->payrollComponentRepository = $payroll_component_repository;
    }

    public function setPayrollComponentRequester(PayrollComponentRequester $payroll_component_requester)
    {
        $this->payrollComponentRequester = $payroll_component_requester;
        return $this;
    }

    public function update()
    {
        DB::transaction(function () {
            $this->updateEachData();
        });
    }

    public function delete()
    {
        DB::transaction(function () {
            $this->deleteEachData();
        });
    }

    private function updateEachData()
    {
        $payroll_settings = $this->payrollComponentRequester->getSetting();
        $gross_component_update = $this->payrollComponentRequester->getGrossComponentUpdate();
        if ($gross_component_update)
            foreach ($gross_component_update as $component) {
                $data = [
                    'payroll_setting_id' => $payroll_settings->id,
                    'name' => $component['key'],
                    'value' => $component['title'],
                    'type' => 'gross',
                    'is_active' => $component['is_active'],
                    'is_taxable' => $component['taxable'],
                    'setting' => json_encode(['percentage' => $component['value']]),
                ];
                $existing_component = $this->payrollComponentRepository->find($component['id']);
                $this->payrollComponentRepository->update($existing_component, $this->withUpdateModificationField($data));
            }
    }

    private function deleteEachData()
    {
        $gross_component_delete = $this->payrollComponentRequester->getGrossComponentDelete();
        if ($gross_component_delete) {
            foreach ($gross_component_delete as $component) {
                $existing_component = $this->payrollComponentRepository->find($component);
                if (!$existing_component) return;
                $existing_component->delete();
            }
        }
    }
}