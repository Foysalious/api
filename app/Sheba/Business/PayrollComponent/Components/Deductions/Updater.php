<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions;

use Illuminate\Support\Facades\DB;
use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequester;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;

class Updater
{
    /*** @var PayrollComponentRepository */
    private $payrollComponentRepository;
    /*** @var PayrollComponentRequester */
    private $payrollComponentRequester;

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
            $this->makeData();
        });
    }

    private function makeData()
    {
        $deduction_components = $this->payrollComponentRequester->getDeduction();

        $payroll_settings = $this->payrollComponentRequester->getSetting();
        foreach ($deduction_components as $component) {
            $data = [
                'payroll_setting_id' => $payroll_settings->id,
                'name' => $component['name'],
                'type' => 'deduction',
                'is_default' => 0,
                'setting' => json_encode([]),
            ];
            $existing_component = $this->payrollComponentRepository->find($component['id']);
            $this->payrollComponentRepository->update($existing_component, $data);
        }
    }

}
