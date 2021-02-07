<?php namespace App\Sheba\Business\PayrollComponent\Components;

use Sheba\Business\PayrollComponent\Components\MedicalAllowance;
use Sheba\Business\PayrollComponent\Components\BasicSalary;
use Sheba\Business\PayrollComponent\Components\Conveyance;
use Sheba\Business\PayrollComponent\Components\HouseRent;
use Sheba\Dal\PayrollComponent\PayrollComponent;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\Type;

class GrossSalaryBreakdown
{
    private $payrollComponentData;

    public function __construct()
    {
        $this->payrollComponentData = [];
    }

    /**
     * @param $payroll_setting
     * @return array
     */
    public function salaryBreakdown($payroll_setting)
    {
        /** @var PayrollComponent $payroll_components */
        $payroll_components = $payroll_setting->components()->where('type', Type::GROSS)->get();
        foreach ($payroll_components as $payroll_component) {
            if ($payroll_component->name == Components::BASIC_SALARY) {
                $this->payrollComponentData[Components::BASIC_SALARY] = (new BasicSalary($payroll_component))->getPercentage();
            }
            if ($payroll_component->name == Components::HOUSE_RENT) {
                $this->payrollComponentData[Components::HOUSE_RENT] = (new HouseRent($payroll_component))->getPercentage();
            }
            if ($payroll_component->name == Components::MEDICAL_ALLOWANCE) {
                $this->payrollComponentData[Components::MEDICAL_ALLOWANCE] = (new MedicalAllowance($payroll_component))->getPercentage();
            }
            if ($payroll_component->name == Components::CONVEYANCE) {
                $this->payrollComponentData[Components::CONVEYANCE] = (new Conveyance($payroll_component))->getPercentage();
            }
        }
        return $this->payrollComponentData;
    }

}