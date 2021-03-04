<?php namespace App\Sheba\Business\PayrollComponent\Components\Additions;

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
        $addition_components = $this->payrollComponentRequester->getAddition();
        $payroll_settings = $this->payrollComponentRequester->getSetting();
        foreach ($addition_components as $component) {
            $this->payrollComponentData[] = [
                'payroll_setting_id' => $payroll_settings->id,
                'name' => $component,
                'type' => 'addition',
                'is_default' => 0,
                'setting' => json_encode([]),
            ];
        }
    }
}
