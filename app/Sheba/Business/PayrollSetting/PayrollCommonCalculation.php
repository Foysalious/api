<?php namespace App\Sheba\Business\PayrollSetting;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepo;
use Sheba\Dal\BusinessOffice\Type as WorkingDaysType;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepo;
use Sheba\Dal\PayrollComponent\TargetType as ComponentTargetType;
use Sheba\Dal\PayrollSetting\PayDayType;
use Sheba\Dal\PayrollSetting\PayrollSetting;

trait PayrollCommonCalculation
{
    /**
     * @param PayrollSetting $payroll_setting
     * @return bool
     */
    public function isPayDay(PayrollSetting $payroll_setting)
    {
        $next_pay_day = $payroll_setting->next_pay_day;
        if (Carbon::now()->format('Y-m-d') == $next_pay_day) return true;
        return false;
    }

    public function nextPayslipGenerationDay(Business $business)
    {
        $payroll_setting = $business->payrollSetting;
        $pay_day_type = $payroll_setting->pay_day_type;
        $next_pay_day = null;
        if ($pay_day_type == PayDayType::FIXED_DATE) {
            $next_pay_day = Carbon::now()->addMonth()->day($payroll_setting->pay_day)->format('Y-m-d');
        } elseif ($pay_day_type == PayDayType::LAST_WORKING_DAY){
            $last_day_of_month = Carbon::now()->addMonth()->lastOfMonth();
            $last_day_of_month = $this->lastWorkingDayOfMonth($business, $last_day_of_month);
            $next_pay_day = $last_day_of_month->format('Y-m-d');
        }
        return $next_pay_day;
    }

    public function getFixPayAmountCalculation($business_member, $package, $on_what, $amount)
    {
        $business_member_salary = $business_member->salary ? floatValFormat($business_member->salary->gross_salary) : 0;
        if ($on_what === PayrollConstGetter::FIXED_AMOUNT) return $amount;
        else if ($on_what === PayrollConstGetter::GROSS_SALARY) return (($business_member_salary * $amount) / 100);
        $component = $package->payrollComponent->where('name', $package->on_what)->where('target_type', ComponentTargetType::EMPLOYEE)->where('target_id', $business_member->id)->first();
        if (!$component) $component = $package->payrollComponent->where('name', $package->on_what)->where(function($query) {
            return $query->where('target_type', null)->orWhere('target_type', ComponentTargetType::GENERAL);
        })->first();
        $percentage = json_decode($component->setting, 1)['percentage'];
        $component_amount = ($business_member_salary * $percentage) / 100;
        return (($component_amount * $amount) / 100);
    }

    public function lastWorkingDayOfMonth($business, $last_day_of_month)
    {
        $business_week_repo = app(BusinessWeekendRepo::class);
        $business_holiday_repo = app(BusinessHolidayRepo::class);
        while ($last_day_of_month) {
            if (!$business_week_repo->isWeekendByBusiness($business, $last_day_of_month) &&
                !$business_holiday_repo->isHolidayByBusiness($business, $last_day_of_month)) break;
            $last_day_of_month = $last_day_of_month->subDay(1);
        }
        return $last_day_of_month;
    }

    public function yearlyTotalGrossAmount($percentage)
    {
        return (($percentage * $this->grosSalary) / 100) * 12;
    }

    public function getNetTaxableIncome($taxable_income, $gender)
    {
        if ($gender === 'Male' ||  $gender === null) return ($taxable_income - PayrollConstGetter::MALE_TAX_EXEMPTED) <= 0 ? 0 : ($taxable_income - PayrollConstGetter::MALE_TAX_EXEMPTED);
        if ($gender === 'Female') return ($taxable_income - PayrollConstGetter::FEMALE_TAX_EXEMPTED) <= 0 ? 0 : ($taxable_income - PayrollConstGetter::FEMALE_TAX_EXEMPTED);

        return ($taxable_income - PayrollConstGetter::SPECIAL_TAX_EXEMPTED) <= 0 ? 0 : ($taxable_income - PayrollConstGetter::SPECIAL_TAX_EXEMPTED);
    }

    public function getTotalBusinessWorkingDays($period, $business_office)
    {
        $working_days_type = $business_office->type;
        $is_weekend_included = $business_office->is_weekend_included;
        $number_of_days = $business_office->number_of_days;
        $total_policy_working_days = $period->count();
        if ($working_days_type === WorkingDaysType::FIXED) return $number_of_days;
        $period_wise_information = new PeriodWiseInformation();
        $period_wise_information = $period_wise_information->setPeriod($period)->setBusinessOffice($business_office)->setIsCalculateAttendanceInfo(0)->get();
        if ($working_days_type === WorkingDaysType::AS_PER_CALENDAR && !$is_weekend_included) return ($total_policy_working_days - $period_wise_information->weekend_count);
        return $total_policy_working_days;
    }

    public function oneWorkingDayAmount($amount, $total_working_days)
    {
        return ($amount / $total_working_days);
    }
    public function totalPenaltyAmountByOneWorkingDay($one_working_day_amount, $penalty_amount)
    {
        return ($one_working_day_amount * $penalty_amount);
    }
}