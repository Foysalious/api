<?php namespace App\Sheba\Business\Salary\Component;

use App\Models\BusinessMember;
use App\Sheba\Business\SalaryLog\ComponentBreakdownLog\Creator;
use App\Sheba\Business\SalaryLog\ComponentBreakdownLog\Requester;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\Dal\Salary\Salary;

class Maker
{
    private $componentData;
    /*** @var BusinessMember */
    private $businessMember;
    /** @var PayrollComponentRepository */
    private $payrollComponentRepository;
    /** @var Requester $componentBreakdownLogRequester;*/
    private $componentBreakdownLogRequester;
    /** * @var Creator $componentBreakdownLogCreator */
    private $componentBreakdownLogCreator;
    private $oldPercentage;
    private $payrollSetting;
    /*** @var Salary */
    private $oldSalary;
    private $newSalary;
    private $managerMember;

    public function __construct($component, BusinessMember $business_member, $new_salary, $old_salary)
    {
        $this->componentData = $component;
        $this->businessMember = $business_member;
        $this->payrollSetting = $this->businessMember->business->payrollSetting;
        $this->oldSalary = $old_salary;
        $this->newSalary = $new_salary;
        $this->payrollComponentRepository = app(PayrollComponentRepository::class);
        $this->componentBreakdownLogRequester = app(Requester::class);
        $this->componentBreakdownLogCreator = app(Creator::class);
    }

    public function setManager($manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function runComponent()
    {
        $global_component = $this->payrollComponentRepository->where('name', $this->componentData['name'])->where('payroll_setting_id', $this->payrollSetting->id)->where('target_type', TargetType::GENERAL)->first();
        $global_component_percentage = floatval(json_decode($global_component->setting, 1)['percentage']);
        $target_component = $this->payrollComponentRepository->where('name', $this->componentData['name'])->where('payroll_setting_id', $this->payrollSetting->id)->where('target_type', TargetType::EMPLOYEE)->where('target_id', $this->businessMember->id)->first();
        $this->oldPercentage = $global_component_percentage;
        if ($this->componentData['value'] != $global_component_percentage) {
            if ($target_component) {
                $this->oldPercentage = floatval(json_decode($target_component->setting, 1)['percentage']);
                $this->updateComponent($target_component);
            }else {
                $this->createComponent($global_component);
            }
        } else {
            if ($target_component) {
                $this->oldPercentage = floatval(json_decode($target_component->setting, 1)['percentage']);
                $this->deleteComponent($target_component);
            }
        }
        $this->grossSalaryBreakdownLogCreate();
    }

    private function updateComponent($target_component)
    {
        $this->payrollComponentRepository->update($target_component, [
            'setting' => json_encode(['percentage' => $this->componentData['value']])
        ]);
    }

    private function createComponent($global_component)
    {
        $this->payrollComponentRepository->create([
            'payroll_setting_id' => $global_component->payroll_setting_id,
            'name' => $global_component->name,
            'value' => $global_component->value,
            'setting' => json_encode(['percentage' => $this->componentData['value']]),
            'type' => $global_component->type,
            'target_type' => TargetType::EMPLOYEE,
            'target_id' => $this->businessMember->id,
            'is_default' => $global_component->is_default,
            'is_active' => $global_component->is_active,
            'is_taxable' => $global_component->is_taxable
        ]);
    }

    private function deleteComponent($target_component)
    {
        $this->payrollComponentRepository->delete($target_component);
    }

    private function grossSalaryBreakdownLogCreate()
    {
        $this->componentBreakdownLogRequester->setBusinessMember($this->businessMember)
            ->setComponentTitle($this->componentData['title'])
            ->setComponentPercentage($this->componentData['value'])
            ->setComponentAmount($this->componentData['amount'])
            ->setOldPercentage($this->oldPercentage)
            ->setOldSalary($this->oldSalary)
            ->setSalary($this->newSalary)
            ->setManagerMember($this->managerMember);
            $this->componentBreakdownLogCreator->setComponentBreakdownLogRequester($this->componentBreakdownLogRequester)->create();
    }
}