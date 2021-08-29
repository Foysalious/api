<?php namespace App\Sheba\Business\ComponentPackage;

use App\Models\BusinessMember;
use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use Carbon\Carbon;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepo;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepo;
use Sheba\Dal\PayrollComponentPackage\TargetType;
use Sheba\Dal\PayrollSetting\PayDayType;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;

class Formatter
{
    use PayrollCommonCalculation;

    private $businessMember;
    private $department;
    private $businessWeekendRepo;
    private $businessHolidayRepo;

    public function __construct()
    {
        $this->businessMember = app(BusinessMember::class);
        $this->department = app(DepartmentRepositoryInterface::class);
        $this->businessWeekendRepo = app(BusinessWeekendRepo::class);
        $this->businessHolidayRepo = app(BusinessHolidayRepo::class);
    }

    public function makePackageData($component)
    {
        $component_packages = $component->componentPackages;
        $package_data = [];
        foreach ($component_packages as $packages) {
            $targets = $packages->packageTargets;
            array_push($package_data, [
                'id' => $packages->id,
                'package_key' => $packages->key,
                'package_name' => $packages->name,
                'is_active' => $packages->is_active,
                'calculation_type' => $packages->calculation_type,
                'is_percentage' => floatval($packages->is_percentage),
                'on_what' => $packages->on_what,
                'amount' => floatval($packages->amount),
                'schedule_type' => $packages->schedule_type,
                'periodic_schedule' => $packages->periodic_schedule,
                'schedule_date' => $packages->schedule_date,
                'target' => $this->getTarget($targets)
            ]);
        }
        return $package_data;
    }

    private function getTarget($targets)
    {
        $data = [];
        foreach ($targets as $target) {
            $data['effective_for'] = $target->effective_for;
            if ($target->effective_for == TargetType::GENERAL) {
                $data['selected'] = null;
                continue;
            }
            $data['selected'][] = [
                'id' => $target->target_id,
                'name' => $this->getTargetDetails($target->effective_for, $target->target_id)['name']
            ];
        }
        return $data;
    }

    private function getTargetDetails($type, $target_id)
    {
        if ($type == TargetType::EMPLOYEE) $target =  $this->businessMember->find($target_id)->profile();
        if($type == TargetType::DEPARTMENT) $target = $this->department->find($target_id);
        return [
            'name' => $target->name
        ];
    }

    public function packageGenerateData(PayrollSetting $payroll_setting, $last_generated_date, $period)
    {
        $current_time = Carbon::now();
        $business_pay_day = $payroll_setting->pay_day;
        if (!empty($last_generated_date)) $current_package_pay_generate_date = Carbon::parse($last_generated_date)->addMonths($period)->format('Y-m-d');
        else if ($payroll_setting->pay_day_type == PayDayType::FIXED_DATE && $current_time->day < $business_pay_day) $current_package_pay_generate_date = $current_time->day($business_pay_day)->format('Y-m-d');
        else if ($payroll_setting->pay_day_type == PayDayType::FIXED_DATE && ($current_time->day > $business_pay_day || $current_time->day == $business_pay_day)) $current_package_pay_generate_date = $current_time->addMonth()->day($business_pay_day)->format('Y-m-d');
        else if ($payroll_setting->pay_day_type == PayDayType::LAST_WORKING_DAY) $current_package_pay_generate_date = $this->nextPayDay($payroll_setting, Carbon::now());

        return ['generated_at' => $current_package_pay_generate_date];
    }

    private function nextPayDay(PayrollSetting $payroll_setting, Carbon $time)
    {
        $business = $payroll_setting->business;
        $last_day_of_month = $time->lastOfMonth();
        return $this->lastWorkingDayOfMonth($business, $last_day_of_month)->format('Y-m-d');
    }
}
