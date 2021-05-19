<?php namespace App\Sheba\Business\Salary\Component;

use App\Models\BusinessMember;
use App\Sheba\Business\SalaryLog\ComponentBreakdownLog\Creator;
use App\Sheba\Business\SalaryLog\ComponentBreakdownLog\Requester;
use Sheba\Dal\PayrollComponent\PayrollComponent;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\Salary\Salary;

class Maker
{
    private $componentData;
    private $newComponentData = [];
    /*** @var BusinessMember */
    private $businessMember;
    /*** @var PayrollComponent */
    private $payrollComponent;
    /** @var PayrollComponentRepository */
    private $payrollComponentRepository;
    /** @var Requester $componentBreakdownLogRequester;*/
    private $componentBreakdownLogRequester;
    /** * @var Creator $componentBreakdownLogCreator */
    private $componentBreakdownLogCreator;
    private $oldPercentage;
    private $salary;
    private $oldAmount;
    private $oldSalaryAmount;
    private $managerMember;
    private $payrollSetting;
    private $isOverwritten;

    public function __construct($component)
    {
        $this->componentData = $component;
        $this->payrollComponentRepository = app(PayrollComponentRepository::class);
        $this->componentBreakdownLogRequester = app(Requester::class);
        $this->componentBreakdownLogCreator = app(Creator::class);
    }

    public function setSalary(Salary $salary)
    {
        $this->salary = $salary;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        if(!$this->salary) $this->salary = $this->businessMember->salary;

        return $this;
    }

    public function setPayrollSetting($payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }

    public function setOldSalaryAmount($old_salary_amount)
    {
        $this->oldSalaryAmount = $old_salary_amount;
        return $this;
    }

    public function setIsOverwritten($is_overwritten)
    {
        $this->isOverwritten = $is_overwritten;
        return $this;
    }

    public function setPayrollComponent(PayrollComponent $payroll_component)
    {
        $this->payrollComponent = $payroll_component;
        $this->oldPercentage = json_decode($payroll_component->setting, 1)['percentage'];
        $this->oldAmount = ($this->oldSalaryAmount * $this->oldPercentage) / 100;
        return $this;
    }

    public function setManagerMember($manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function createCoWorkerGrossComponent()
    {
        if($this->isOverwritten) {
            $this->makeData();
            $this->payrollComponentRepository->create($this->newComponentData);
        }
        $this->grossSalaryBreakdownLogCreate($this->componentData['title'], $this->componentData['value'], $this->componentData['amount']);
    }

    public function updateCoWorkerGrossComponent()
    {
        $this->makeData();
        $this->payrollComponentRepository->update($this->payrollComponent, $this->newComponentData);
        $this->grossSalaryBreakdownLogCreate($this->componentData['title'], $this->componentData['value'], $this->componentData['amount']);
    }

    private function makeData()
    {
        $this->newComponentData = [
            'payroll_setting_id' => $this->payrollSetting->id,
            'name' => $this->componentData['name'],
            'value' => $this->componentData['title'],
            'setting' => json_encode(['percentage' => $this->componentData['value']]),
            'type' => Type::GROSS,
            'target_type' => TargetType::EMPLOYEE,
            'target_id' => $this->businessMember->id,
            'is_default' => $this->componentData['is_default'],
            'is_active' => $this->componentData['is_active'],
            'is_taxable' => $this->componentData['is_taxable']
        ];
    }

    private function grossSalaryBreakdownLogCreate($component_title, $component_value, $amount)
    {
        $this->componentBreakdownLogRequester->setBusinessMember($this->businessMember)
            ->setManagerMember($this->managerMember)
            ->setSalary($this->salary)
            ->setComponentTitle($component_title)
            ->setComponentPercentage($component_value)
            ->setOldPercentage($this->oldPercentage)
            ->setOldAmount($this->oldAmount)
            ->setComponentAmount($amount);
            $this->componentBreakdownLogCreator->setComponentBreakdownLogRequester($this->componentBreakdownLogRequester)->create();
    }
}