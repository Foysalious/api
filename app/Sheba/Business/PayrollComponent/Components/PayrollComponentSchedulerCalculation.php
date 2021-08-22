<?php namespace App\Sheba\Business\PayrollComponent\Components;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\PayrollComponent\Components\Deductions\BusinessMemberPolicyRulesCalculator;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\PayrollComponentPackage\TargetType as PackageTargetType;

class PayrollComponentSchedulerCalculation
{

    private $business;
    private $businessMember;
    private $department;
    private $additionData = [];
    private $deductionData = [];
    private $businessPayDay;
    private $payrollSetting;
    private $packageCalculator;
    /*** @var BusinessMemberPolicyRulesCalculator */
    private $businessMemberPolicyRulesCalculator;
    private $taxComponentData = [];
    private $timeFrame;
    private $joiningDate;

    /**
     * PayrollComponentSchedulerCalculation constructor.
     */
    public function __construct()
    {
        $this->businessMemberPolicyRulesCalculator = new BusinessMemberPolicyRulesCalculator();
        $this->packageCalculator = new PackageCalculator();
    }
    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        $this->payrollSetting = $this->business->payrollSetting;
        $this->businessPayDay = $this->payrollSetting->pay_day;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $role = $this->businessMember->role;
        $this->department = $role? $role->businessDepartment : null;
        return $this;
    }
    public function setTimeFrame($time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function getPayrollComponentCalculationBreakdown()
    {
        $addition = $this->getAdditionComponent();
        $deduction = $this->getDeductionComponent();
        return ['payroll_component' => array_merge($addition, $deduction)];
    }

    private function getAdditionComponent()
    {
        $components = $this->payrollSetting->components()->where('type', Type::ADDITION)->where(function($query) {
            return $query->where('is_default', 1)->orWhere('is_active',1);
        })->orderBy('type')->get();
        $total_addition = 0;
        foreach ($components as $component) {
            if (!$component->is_default) $total_addition += $this->calculatePackage($component->componentPackages->where('is_active', 1), $component);
            $this->additionData['addition'][$component->name] = $total_addition;
            $total_addition = 0;
        }
        return $this->additionData;
    }
    private function getDeductionComponent()
    {
        $components = $this->payrollSetting->components()->where('type', Type::DEDUCTION)->where(function($query) {
            return $query->where('is_default', 1)->orWhere('is_active',1);
        })->orderBy('type')->get();
        $default_deduction_component_data = $this->businessMemberPolicyRulesCalculator->setBusiness($this->business)->setBusinessMember($this->businessMember)->setTimeFrame($this->timeFrame)->setAdditionBreakdown($this->additionData)->calculate();

        $total_deduction = 0;
        foreach ($components as $component) {
            if (!$component->is_default) {
                $total_deduction += $this->calculatePackage($component->componentPackages->where('is_active', 1), $component);
                $this->deductionData['deduction'][$component->name] = $total_deduction;
                $total_deduction = 0;
                continue;
            }
            $this->deductionData['deduction'][$component->name] = $default_deduction_component_data[$component->name];
        }
        return $this->deductionData;
    }
    private function calculatePackage($packages, $component)
    {
        $total_package_amount = 0;
        $taxable_packages = [];
        foreach ($packages as $package) {
            $employee_target = $package->packageTargets->where('effective_for', PackageTargetType::EMPLOYEE)->where('target_id', $this->businessMember->id);
            $department_target = $this->department ? $package->packageTargets->where('effective_for', PackageTargetType::DEPARTMENT)->where('target_id', $this->department->id) : null;
            $global_target =  $package->packageTargets->where('effective_for', PackageTargetType::GENERAL);
            $target_amount = 0;
            if (!$employee_target->isEmpty() || !$department_target->isEmpty() || !$global_target->isEmpty()) {
                $package_component = $package->payrollComponent;
                if ($package_component->is_taxable){
                    $taxable_packages[] = $package;
                }
                $target_amount = $this->packageCalculator->setBusinessMember($this->businessMember)->setPayrollSetting($this->payrollSetting)->setPackage($package)->calculate();
            }
            $total_package_amount += $target_amount;
        }
        if($taxable_packages) $this->taxComponentData[$component->id] = $taxable_packages;
        return $total_package_amount;
    }

    public function getPackageGenerateData()
    {
        return $this->packageCalculator->getPackageGenerateData();
    }
    public function getTaxComponentData() {
        return $this->taxComponentData;
    }
}