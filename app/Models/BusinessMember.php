<?php namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Helpers\TimeFrame;

class BusinessMember extends Model
{
    protected $guarded = ['id',];
    protected $table = 'business_member';
    protected $dates = ['join_date'];
    protected $casts = ['is_super' => 'int'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function actions()
    {
        return $this->belongsToMany(Action::class);
    }

    public function role()
    {
        return $this->belongsTo(BusinessRole::class, 'business_role_id');
    }

    public function department()
    {
        return $this->role ? $this->role->businessDepartment : null;
    }

    public function isSuperAdmin()
    {
        return $this->is_super;
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceOfToday()
    {
        return $this->hasMany(Attendance::class)->where('date', (Carbon::now())->toDateString())->first();
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function manager()
    {
        return $this->belongsTo(BusinessMember::class, 'manager_id');
    }

    /**
     * @param Carbon $date
     * @return bool
     */
    public function isOnLeaves(Carbon $date)
    {
        $date = $date->toDateString();
        $leave = $this->leaves()->accepted()->whereRaw("('$date' BETWEEN start_date AND end_date)")->first();
        return !!$leave;
    }

    /**
     * @param $leave_type_id
     * @return int
     */
    public function getCountOfUsedLeaveDaysByTypeOnAFiscalYear($leave_type_id)
    {
        $time_frame = $this->getBusinessFiscalPeriod();

        $leaves = $this->leaves()->accepted()->between($time_frame)->with('leaveType')->whereHas('leaveType', function ($leave_type) use ($leave_type_id) {
            return $leave_type->where('id', $leave_type_id);
        })->get();

        return $this->getCountOfUsedDays($leaves, $time_frame);
    }

    public function getCountOfUsedLeaveDaysByFiscalYear(Collection $leaves)
    {
        $time_frame = $this->getBusinessFiscalPeriod();
        return $this->getCountOfUsedDays($leaves, $time_frame);
    }

    public function getBusinessFiscalPeriod()
    {
        $business_fiscal_start_month = $this->business->fiscal_year ?: Business::BUSINESS_FISCAL_START_MONTH;
        $time_frame = new TimeFrame();
        return $time_frame->forAFiscalYear(Carbon::now(), $business_fiscal_start_month);
    }

    private function getCountOfUsedDays(Collection $leaves, $time_frame)
    {
        $used_days = 0;
        $business_weekend = $dates_of_holidays_formatted = [];

        $leave_day_into_holiday_or_weekend = 0;
        if (!$this->business->is_sandwich_leave_enable) {
            $business_holiday = app(BusinessHolidayRepoInterface::class)->getAllByBusiness($this->business);
            $data = [];
            foreach ($business_holiday as $holiday) {
                $start_date = $holiday->start_date;
                $end_date = $holiday->end_date;
                for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                    $data[] = $d->format('Y-m-d');
                }
            }
            $dates_of_holidays_formatted = $data;
            $business_weekend = app(BusinessWeekendRepoInterface::class)->getAllByBusiness($this->business)->pluck('weekday_name')->toArray();
        }

        $leaves->each(function ($leave) use (&$used_days, $time_frame, $business_weekend, $dates_of_holidays_formatted, $leave_day_into_holiday_or_weekend) {
            if ($this->isLeaveFullyInAFiscalYear($time_frame, $leave)) {
                $used_days += $leave->total_days - $leave_day_into_holiday_or_weekend;
                return;
            }

            $start_date = $leave->start_date->lt($time_frame->start) ? $time_frame->start : $leave->start_date;
            $end_date = $leave->end_date->gt($time_frame->end) ? $time_frame->end : $leave->end_date;

            $period = CarbonPeriod::create($start_date, $end_date);
            foreach ($period as $date) {
                $day_name_in_lower_case = strtolower($date->format('l'));
                if (in_array($day_name_in_lower_case, $business_weekend)) { $leave_day_into_holiday_or_weekend++; continue; }
                if (in_array($date->toDateString(), $dates_of_holidays_formatted)) { $leave_day_into_holiday_or_weekend++; continue; }
            }

            $used_days += ($end_date->diffInDays($start_date) + 1) - $leave_day_into_holiday_or_weekend;
        });

        return (int)$used_days;
    }

    private function isLeaveFullyInAFiscalYear($fiscal_year_time_frame, Leave $leave)
    {
        return $leave->start_date->between($fiscal_year_time_frame->start, $fiscal_year_time_frame->end) &&
            $leave->end_date->between($fiscal_year_time_frame->start, $fiscal_year_time_frame->end);
    }
}
