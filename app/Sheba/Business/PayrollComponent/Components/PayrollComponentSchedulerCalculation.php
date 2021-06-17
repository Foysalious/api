<?php namespace App\Sheba\Business\PayrollComponent\Components;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\ComponentPackage\Formatter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Sheba\Business\Prorate\Creator as ProrateCreator;
use Sheba\Business\Prorate\Requester as ProrateRequester;
use Sheba\Business\Prorate\Updater as ProrateUpdater;
use Sheba\Dal\Attendance\Contract as AttendanceRepositoryInterface;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessMemberLeaveType\Contract as BusinessMemberLeaveTypeInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\OfficePolicyRule\ActionType;
use Sheba\Dal\PayrollComponent\TargetType as ComponentTargetType;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\PayrollComponentPackage\CalculationType;
use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackageRepository;
use Sheba\Dal\PayrollComponentPackage\ScheduleType;
use Sheba\Dal\PayrollComponentPackage\TargetType as PackageTargetType;
use Sheba\Helpers\TimeFrame;

class PayrollComponentSchedulerCalculation
{
    const FIXED_AMOUNT = 'fixed_amount';
    const GROSS_SALARY = 'gross';

    private $business;
    private $businessMember;
    private $department;
    /*** @var PayrollComponentPackageRepository $payrollComponentPackageRepository */
    private $payrollComponentPackageRepository;
    private $timeFrame;
    private $attendanceRepositoryInterface;
    /*** @var BusinessHolidayRepoInterface $businessHolidayRepo*/
    private $businessHolidayRepo;
    /*** @var BusinessWeekendRepoInterface $businessWeekendRepo*/
    private $businessWeekendRepo;
    private $businessMemberLeaveTypeRepo;
    private $additionData = [];
    private $deductionData = [];
    private $prorateRequester;
    private $prorateUpdater;
    private $prorateCreator;
    private $businessPayDay;
    private $payrollSetting;

    /**
     * PayrollComponentSchedulerCalculation constructor.
     */
    public function __construct()
    {
        $this->payrollComponentPackageRepository = app(PayrollComponentPackageRepository::class);
        $this->timeFrame = app(TimeFrame::class);
        $this->attendanceRepositoryInterface = app(AttendanceRepositoryInterface::class);
        $this->businessHolidayRepo = app(BusinessHolidayRepoInterface::class);
        $this->businessWeekendRepo = app(BusinessWeekendRepoInterface::class);
        $this->businessMemberLeaveTypeRepo = app(BusinessMemberLeaveTypeInterface::class);
        $this->prorateRequester = app(ProrateRequester::class);
        $this->prorateCreator = app(ProrateCreator::class);
        $this->prorateUpdater = app(ProrateUpdater::class);
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
            if (!$component->is_default) $total_addition += $this->calculatePackage($component->componentPackages->where('is_active', 1));
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
        $default_deduction_component_data = $this->calculateBusinessMemberPolicyRulesDeduction();
        $total_deduction = 0;
        foreach ($components as $component) {
            if (!$component->is_default) {
                $total_deduction += $this->calculatePackage($component->componentPackages->where('is_active', 1));
                $this->deductionData['deduction'][$component->name] = $total_deduction;
                $total_deduction = 0;
                continue;
            }
            $this->deductionData['deduction'][$component->name] = $default_deduction_component_data[$component->name];
        }
        return $this->deductionData;
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
        $calculation_type = $package->calculation_type; // Package Calculation Type -> FIX PAY or VARIABLE AMOUNT
        $on_what = $package->on_what; // Package Calculation on which Gross Salary Component
        $amount = floatValFormat($package->amount); // Package Amount Percentage or Fixed Amount of tk
        $schedule_type = $package->schedule_type; // Package Schedule Type Periodically or Fixed Month
        $schedule_date = $package->schedule_date; // On which month Package should be availed

        $final_amount = 0;
        if ($calculation_type == CalculationType::VARIABLE_AMOUNT) return $final_amount;
        $current_time = Carbon::now();
        $business_member_salary = $this->businessMember->salary ? floatValFormat($this->businessMember->salary->gross_salary) : 0;
        if ($schedule_type == ScheduleType::FIXED_DATE && $current_time->month != $schedule_date) return $final_amount;
        $next_generated_month = Carbon::parse($package->generated_at)->format('Y-m'); // Calculate Package Generation Day Based on Last Generated Day
        if ($schedule_type == ScheduleType::PERIODICALLY && $next_generated_month != $current_time->format('Y-m')) return $final_amount;

        if ($calculation_type == CalculationType::FIX_PAY_AMOUNT) {
            if ($on_what == self::FIXED_AMOUNT) $final_amount = $amount;
            else if ($on_what == self::GROSS_SALARY) $final_amount = (($business_member_salary * $amount) / 100);
            else {
                $component = $package->payrollComponent->where('name', $package->on_what)->where('target_type', ComponentTargetType::EMPLOYEE)->where('target_id', $this->businessMember->id)->first();
                if (!$component) $component = $package->payrollComponent->where('name', $package->on_what)->where('target_type', ComponentTargetType::GENERAL)->first();
                $percentage = json_decode($component->setting, 1)['percentage'];
                $component_amount = ($business_member_salary * $percentage) / 100;
                $final_amount = ( $component_amount * $amount ) / 100;
            }
        }
        if ($schedule_type != ScheduleType::PERIODICALLY) return $final_amount;

        DB::transaction(function () use ($package, $current_time){
            $package_generate_data = (new Formatter)->packageGenerateData($this->payrollSetting, $current_time->format('Y-m-d'), $package->periodic_schedule);
            $this->payrollComponentPackageRepository->update($package, $package_generate_data);
        });
        return $final_amount;
    }
    private function calculateBusinessMemberPolicyRulesDeduction()
    {
        $business_office = $this->business->officeHour;
        $office_start_time = Carbon::parse($business_office->start_time);
        $office_end_time = Carbon::parse($business_office->end_time);
        $start_grace_time = $business_office->start_grace_time;
        $end_grace_time = $business_office->end_grace_time;
        $office_start_time_with_grace = $office_start_time->addMinutes(intval($start_grace_time))->format('h:i:s');
        $office_end_time_with_grace = $office_end_time->addMinutes(intval($end_grace_time))->format('h:i:s');
        $is_grace_period_policy_enable = $business_office->is_grace_period_policy_enable;
        $is_late_checkin_early_checkout_policy_enable = $business_office->is_late_checkin_early_checkout_policy_enable;
        $is_for_late_checkin = $business_office->is_for_late_checkin;
        $is_for_early_checkout = $business_office->is_for_early_checkout;
        $is_unpaid_leave_policy_enable = $business_office->is_unpaid_leave_policy_enable;

        if (!$is_grace_period_policy_enable && !$is_late_checkin_early_checkout_policy_enable && !$is_unpaid_leave_policy_enable) return 0;
        $start_date = Carbon::now()->day($this->businessPayDay)->subMonth()->format('Y-m-d');
        $end_date = Carbon::now()->day($this->businessPayDay)->subDay()->format('Y-m-d');
        $time_frame = $this->timeFrame->forDateRange($start_date, $end_date);
        $attendances = $this->attendanceRepositoryInterface->getAllAttendanceByBusinessMemberFilteredWithYearMonth($this->businessMember, $time_frame);
        $business_holiday = $this->businessHolidayRepo->getAllByBusiness($this->business);
        $business_weekend = $this->businessWeekendRepo->getAllByBusiness($this->business)->pluck('weekday_name')->toArray();
        $business_member_leave = $this->businessMember->leaves()->accepted()->between($time_frame)->get();
        list($leaves, $leaves_date_with_half_and_full_day) = $this->formatLeaveAsDateArray($business_member_leave);
        $period = CarbonPeriod::create($time_frame->start, $time_frame->end);
        $dates_of_holidays_formatted = $this->getHolidaysFormatted($business_holiday);
        $total_late_checkin = 0;
        $total_early_checkout = 0;
        $total_late_checkin_or_early_checkout = 0;
        $total_present = 0;
        $total_working_days = 0;
        $grace_time_over = 0;
        $business_member_attendance = $this->getBusinessMemberAttendanceTime($attendances, $business_office);
        foreach ($period as $date) {
            $is_weekend_or_holiday = $this->isWeekendHoliday($date, $business_weekend, $dates_of_holidays_formatted);
            $is_on_leave = $this->isLeave($date, $leaves);
            if (!$is_weekend_or_holiday && !$is_on_leave) {
                $total_working_days++;
                if (array_key_exists($date->format('y-m-d'), $business_member_attendance)){
                    if($business_member_attendance[$date->format('y-m-d')]['checkin_time'] > $office_start_time || $business_member_attendance[$date->format('y-m-d')]['checkout_time'] < $office_end_time) $total_late_checkin_or_early_checkout++;
                    if($business_member_attendance[$date->format('y-m-d')]['checkin_time'] > $office_start_time) $total_late_checkin++;
                    if($business_member_attendance[$date->format('y-m-d')]['checkout_time'] < $office_end_time) $total_early_checkout++;
                    if($business_member_attendance[$date->format('y-m-d')]['checkin_time'] > $office_start_time_with_grace || $business_member_attendance[$date->format('y-m-d')]['checkout_time'] < $office_end_time_with_grace) $grace_time_over++;

                    $total_present++;
                }
            }
        }
        $late_checkin_early_checkout_days = ($is_for_late_checkin && $is_for_early_checkout) ? $total_late_checkin_or_early_checkout : ($is_for_late_checkin && !$is_for_early_checkout) ? $total_late_checkin : (!$is_for_late_checkin && $is_for_early_checkout) ? $total_early_checkout : 0;
        $total_absent = ($total_working_days - $total_present);
        $attendance_adjustment = 0;
        $leave_adjustment = 0;
        if ($is_grace_period_policy_enable) $attendance_adjustment += $this->gracePolicyAction($grace_time_over, $total_working_days);
        if ($is_late_checkin_early_checkout_policy_enable) $attendance_adjustment += $this->lateCheckInEarlyCheckoutPolicyAction($late_checkin_early_checkout_days, $total_working_days);
        if ($is_unpaid_leave_policy_enable) $leave_adjustment += $this->unpaidLeavePolicyAction($total_absent, $total_working_days);
        return ['attendance_adjustment' => $attendance_adjustment, 'leave_adjustment' => $leave_adjustment, 'tax' => 0];
    }
    private function isWeekendHoliday($date, $weekend_day, $dates_of_holidays_formatted)
    {
        return $this->isWeekend($date, $weekend_day)
            || $this->isHoliday($date, $dates_of_holidays_formatted);

    }
    private function isWeekend(Carbon $date, $weekend_day)
    {
        return in_array(strtolower($date->format('l')), $weekend_day);
    }
    private function isHoliday(Carbon $date, $holidays)
    {
        return in_array($date->format('Y-m-d'), $holidays);
    }
    private function isLeave(Carbon $date, array $leaves)
    {
        return in_array($date->format('Y-m-d'), $leaves);
    }
    private function formatLeaveAsDateArray($business_member_leave)
    {
        $business_member_leaves_date = [];
        $business_member_leaves_date_with_half_and_full_day = [];
        $business_member_leave->each(function ($leave) use (&$business_member_leaves_date, &$business_member_leaves_date_with_half_and_full_day) {
            $leave_period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($leave_period as $date) {
                array_push($business_member_leaves_date, $date->toDateString());
                $business_member_leaves_date_with_half_and_full_day[$date->toDateString()] = [
                    'is_half_day_leave' => $leave->is_half_day,
                    'which_half_day' => $leave->half_day_configuration,
                ];
            }
        });

        return [array_unique($business_member_leaves_date), $business_member_leaves_date_with_half_and_full_day];
    }
    private function gracePolicyAction($days, $total_working_days)
    {
        $grace_policy_rules = $this->business->gracePolicy()->where(function ($query) use ($days) {
            $query->where('from_days', '<=', $days);
            $query->where('to_days', '>=', $days);
        })->first();

        return $this->rulesPolicyCalculation($grace_policy_rules, $total_working_days);
    }
    private function lateCheckInEarlyCheckoutPolicyAction($days, $total_working_days)
    {
        $checkin_checkout_policy_rules = $this->business->checkinCheckoutPolicy()->where(function ($query) use ($days) {
            $query->where('from_days', '<=', $days);
            $query->where('to_days', '>=', $days);
        })->first();
        return $this->rulesPolicyCalculation($checkin_checkout_policy_rules, $total_working_days);
    }
    private function unpaidLeavePolicyAction($days, $total_working_days)
    {
        $unpaid_leave_policy_rules = $this->business->unpaidLeavePolicy()->where(function ($query) use ($days) {
            $query->where('from_days', '<=', $days);
            $query->where('to_days', '>=', $days);
        })->first();
        return $this->rulesPolicyCalculation($unpaid_leave_policy_rules, $total_working_days);
    }
    private function rulesPolicyCalculation($policy_rules, $total_working_days)
    {
        $policy_total = 0;

        if (!$policy_rules) return $policy_total;
        $action = $policy_rules->action;
        if ($action === ActionType::NO_PENALTY) return $policy_total;
        if ($action === ActionType::CASH_PENALTY) return floatValFormat($policy_rules->penalty_amount);
        $business_member_salary = $this->businessMember->salary ? floatValFormat($this->businessMember->salary->gross_salary) : 0;
        if ($action === ActionType::LEAVE_ADJUSTMENT){
            $penalty_type = intval($policy_rules->penalty_type);
            $penalty_amount = floatValFormat($policy_rules->penalty_amount);
            $existing_prorate = $this->businessMemberLeaveTypeRepo->where('business_member_id', $this->businessMember->id)->where('leave_type_id', $penalty_type)->first();
            $total_leave_type_days = $existing_prorate ? floatValFormat($existing_prorate->total_Days) : floatValFormat($this->business->leaveTypes->find($penalty_type)->total_days);
            if ($total_leave_type_days < $penalty_amount) {
                $one_working_day_amount = $this->oneWorkingDayAmount($business_member_salary,  floatValFormat($total_working_days));
                return $this->totalPenaltyAmountByOneWorkingDay($one_working_day_amount, $penalty_amount);
            }
            $note = 'Punishment';
            $total_leave_type_days_after_penalty = $total_leave_type_days - $penalty_amount;
            $this->prorateRequester->setLeaveTypeId($penalty_type)
                ->setTotalDays($total_leave_type_days_after_penalty)
                ->setNote($note);
            if ($existing_prorate) $this->prorateUpdater->setRequester($this->prorateRequester)->setBusinessMemberLeaveType($existing_prorate)->update();
            else $this->prorateCreator->setRequester($this->prorateRequester)->create();

            return $policy_total;
        }
        if ($action === ActionType::SALARY_ADJUSTMENT) {
            $penalty_type = $policy_rules->penalty_type;
            $penalty_amount = floatValFormat($policy_rules->penalty_amount);
            $gross_component = $this->payrollSetting->components->where('name', $penalty_type)->where('type', 'gross')->where('target_type', 'employee')->where('target_id', $this->businessMember)->first();
            if (!$gross_component) $gross_component = $this->payrollSetting->components->where('name', $penalty_type)->where('type', 'gross')->where('target_type', 'employee')->where('target_id', $this->businessMember)->first();
            if ($gross_component) {
                $percentage = floatValFormat(json_decode($gross_component->setting, 1)['percentage']);
                $amount = ($business_member_salary * $percentage) / 100;
                $one_working_day_amount = $this->oneWorkingDayAmount($amount,  floatValFormat($total_working_days));
                return $this->totalPenaltyAmountByOneWorkingDay($one_working_day_amount, $penalty_amount);
            }
            $addition_component = $this->payrollSetting->components->where('name', $penalty_type)->where('type', 'addition')->first();
            if ($addition_component) {
                $amount = $this->additionData['addition'][$penalty_type];
                $one_working_day_amount = $this->oneWorkingDayAmount($amount,  floatValFormat($total_working_days));
                return ($one_working_day_amount * $penalty_amount);
            }
            if (!$gross_component && !$addition_component){
                $one_working_day_amount = $this->oneWorkingDayAmount($business_member_salary,  floatValFormat($total_working_days));
                return $this->totalPenaltyAmountByOneWorkingDay($one_working_day_amount, $penalty_amount);
            }
        }

        return $policy_total;
    }
    private function oneWorkingDayAmount($amount, $total_working_days)
    {
        return ($amount / $total_working_days);
    }
    private function totalPenaltyAmountByOneWorkingDay($one_working_day_amount, $penalty_amount)
    {
        return ($one_working_day_amount * $penalty_amount);
    }
    private function getBusinessMemberAttendanceTime($attendances, $business_office)
    {
        $business_member_attendance = [];
        foreach ($attendances as $attendance){
            $business_member_attendance[$attendance->date] = [
                'checkin_time' => Carbon::parse($attendance->checkin_time)->format('h:i:s'),
                'checkout_time' => $attendance->checkout_time ? Carbon::parse($attendance->checkout_time)->format('h:i:s') : Carbon::parse($business_office->end_time)->format('h:i:s'),
            ];
        }
        return $business_member_attendance;
    }
    private function getHolidaysFormatted($business_holiday)
    {
        $data = [];
        foreach ($business_holiday as $holiday) {
            $start_date = Carbon::parse($holiday->start_date);
            $end_date = Carbon::parse($holiday->end_date);
            for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                $data[] = $d->format('Y-m-d');
            }
        }
        return $data;
    }
}