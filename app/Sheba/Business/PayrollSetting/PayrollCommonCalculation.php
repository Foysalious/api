<?php namespace App\Sheba\Business\PayrollSetting;


use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepo;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepo;
use Sheba\Dal\PayrollComponent\TargetType as ComponentTargetType;

trait PayrollCommonCalculation
{
    public function getFixPayAmountCalculation($business_member, $package, $on_what, $amount)
    {
        $business_member_salary = $business_member->salary ? floatValFormat($business_member->salary->gross_salary) : 0;
        if ($on_what == self::FIXED_AMOUNT) return $amount;
        else if ($on_what == self::GROSS_SALARY) return (($business_member_salary * $amount) / 100);
        $component = $package->payrollComponent->where('name', $package->on_what)->where('target_type', ComponentTargetType::EMPLOYEE)->where('target_id', $business_member->id)->first();
        if (!$component) $component = $package->payrollComponent->where('name', $package->on_what)->where('target_type', ComponentTargetType::GENERAL)->first();
        $percentage = json_decode($component->setting, 1)['percentage'];
        $component_amount = ($business_member_salary * $percentage) / 100;
        $final_amount = ( $component_amount * $amount ) / 100;
        return $final_amount;
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
}