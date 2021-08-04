<?php namespace App\Sheba\Business\PayrollComponent\Components\GrossComponents;

use Illuminate\Support\Facades\DB;
use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequester;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;
    
    /** @var PayrollComponentRequester */
    private $payrollComponentRequester;
    /** * @var PayrollComponentRepository */
    private $payrollComponentRepository;
    private $grossComponentData = [];

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
        DB::transaction(function () {
            if ($this->grossComponentData) $this->payrollComponentRepository->create($this->withCreateModificationField($this->grossComponentData));
        });
    }

    public function makeData()
    {
        $payroll_settings = $this->payrollComponentRequester->getSetting();
        $gross_component_add = $this->payrollComponentRequester->getGrossComponentAdd();
        if ($gross_component_add)
            foreach ($gross_component_add as $component) {
                $this->grossComponentData = [
                    'payroll_setting_id' => $payroll_settings->id,
                    'name' => $component['key'],
                    'value' => $component['title'],
                    'type' => 'gross',
                    'target_type' => TargetType::GENERAL,
                    'is_default' => 0,
                    'is_active' => $component['is_active'],
                    'is_taxable' => $component['taxable'],
                    'setting' => json_encode(['percentage' => $component['value']]),
                ];
            }
    }
}