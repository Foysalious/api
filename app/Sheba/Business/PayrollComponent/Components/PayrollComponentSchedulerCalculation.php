<?php namespace App\Sheba\Business\PayrollComponent\Components;

use App\Models\Business;
use App\Models\BusinessMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\PayrollComponent\TargetType as ComponentTargetType;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\PayrollComponentPackage\CalculationType;
use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackageRepository;
use Sheba\Dal\PayrollComponentPackage\ScheduleType;
use Sheba\Dal\PayrollComponentPackage\TargetType as PackageTargetType;

class PayrollComponentSchedulerCalculation
{
    const FIXED_AMOUNT = 'fixed_amount';
    const GROSS_SALARY = 'gross';

    private $business;
    private $businessMember;
    private $department;
    /*** @var PayrollComponentPackageRepository $payrollComponentPackageRepository */
    private $payrollComponentPackageRepository;

    public function __construct()
    {
        $this->payrollComponentPackageRepository = app(PayrollComponentPackageRepository::class);
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $role = $this->businessMember->role;
        $this->department = $role? $role->businessDepartment : null;
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
        $components = $this->business->payrollSetting->components()->where('type', Type::ADDITION)->where('is_active', 1)->orderBy('name')->get();
        $total_addition = 0;
        $data = [];
        foreach ($components as $component) {
            if (!$component->is_default) $total_addition += $this->calculatePackage($component->componentPackages);
            $data['addition'][$component->name] = $total_addition;
            $total_addition = 0;
        }
        return $data;
    }

    private function getDeductionComponent()
    {
        $components = $this->business->payrollSetting->components()->where('type', Type::DEDUCTION)->where('is_active', 1)->orderBy('name')->get();
        //$this->calculateDeductionFromPolicyRules();
        $total_deduction = 0;
        $data = [];
        foreach ($components as $component) {
            if (!$component->is_default) $total_deduction += $this->calculatePackage($component->componentPackages);
            $data['deduction'][$component->name] = $total_deduction;
            $total_deduction = 0;
        }
        return $data;
    }

    private function calculatePackage($packages)
    {
        $total_package_amount = 0;
        foreach ($packages as $package) {
            $employee_target = $package->packageTargets->where('effective_for', PackageTargetType::EMPLOYEE)->where('target_id', $this->businessMember->id);
            $department_target = $this->department ? $package->packageTargets->where('effective_for', PackageTargetType::DEPARTMENT)->where('target_id', $this->department->id) : null;
            $global_target =  $package->packageTargets->where('effective_for', PackageTargetType::GENERAL);
            $target_amount = 0;
            if ($employee_target || $department_target || $global_target) $target_amount = $this->calculateBusinessMemberPackage($package);

            $total_package_amount += $target_amount;
        }

        return $total_package_amount;
    }

    private function calculateBusinessMemberPackage($package)
    {
        $calculation_type = $package->calculation_type;
        $is_percentage = $package->is_percentage;
        $on_what = $package->on_what;
        $amount = floatValFormat($package->amount);
        $schedule_type = $package->schedule_type;
        $periodic_schedule = $package->periodic_schedule;
        $schedule_date = $package->schedule_date;

        $current_time = Carbon::now();
        $final_amount = 0;
        if ($schedule_type == ScheduleType::FIXED_DATE && $current_time->month != $schedule_date) return $final_amount;
        $next_generated_date = Carbon::parse($package->generated_at)->addMonths($periodic_schedule)->format('Y-m');
        if ($schedule_type == ScheduleType::PERIODICALLY && $next_generated_date != $current_time->format('Y-m')) return $final_amount;

        if ($calculation_type == CalculationType::FIX_PAY_AMOUNT) {
            if ($on_what == self::FIXED_AMOUNT) $final_amount = $amount;
            else if ($on_what == self::GROSS_SALARY) $final_amount = (($this->businessMember->salary->gross_salary * $amount) / 100);
            else {
                $component = $package->payrollComponent->where('name', $package->on_what)->where('target_type', ComponentTargetType::EMPLOYEE)->where('target_id', $this->businessMember->id)->first();
                if (!$component) $component = $package->payrollComponent->where('name', $package->on_what)->where('target_type', ComponentTargetType::GENERAL)->first();
                $percentage = json_decode($component->setting, 1)['percentage'];
                $component_amount = ($this->businessMember->salary->gross_salary * $percentage) / 100;
                $final_amount = ( $component_amount * $amount ) / 100;
            }
        }
        DB::transaction(function () use ($package, $current_time){
            $this->payrollComponentPackageRepository->update($package, ['generated_at' => $current_time->format('Y-m-d')]);
        });
        return $final_amount;
    }
}