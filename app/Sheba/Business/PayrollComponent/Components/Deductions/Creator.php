<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions;

use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequester;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;

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

    public function create()
    {
        $this->makeData();
        $this->payrollComponentRepository->insert($this->payrollComponentData);
    }

    private function makeData()
    {
        $deduction_components = $this->payrollComponentRequester->getDeduction();
        $payroll_settings = $this->payrollComponentRequester->getSetting();
        foreach ($deduction_components as $component) {
            $this->payrollComponentData[] = [
                'payroll_setting_id' => $payroll_settings->id,
                'name' => $component,
                'type' => 'deduction',
                'is_default' => 0,
                'setting' => json_encode([]),
            ];
        }
    }

}
