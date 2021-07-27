<?php namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use Sheba\Dal\Salary\Salary;
use Sheba\Helpers\TimeFrame;
use Sheba\Business\BusinessMember\Events\BusinessMemberCreated;
use Sheba\Business\BusinessMember\Events\BusinessMemberUpdated;
use Sheba\Business\BusinessMember\Events\BusinessMemberDeleted;

class BusinessMember extends Model
{
    protected $guarded = ['id',];
    protected $dates = ['join_date'];
    protected $casts = ['is_super' => 'int'];

    protected $dispatchesEvents = [
        'created' => BusinessMemberCreated::class,
        'updated' => BusinessMemberUpdated::class,
        'deleted' => BusinessMemberDeleted::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $table = config('database.connections.mysql.database') . '.business_member';
        $this->setTable($table);
    }
    
    public function setTable($table)
    {
        $this->table = $table;
    }

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

    public function salary()
    {
        return $this->hasOne(Salary::class);
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

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'invited']);
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

        $business_holiday = app(BusinessHolidayRepoInterface::class)->getAllDateArrayByBusiness($this->business);
        $business_weekend = app(BusinessWeekendRepoInterface::class)->getAllByBusiness($this->business)->pluck('weekday_name')->toArray();

        return $this->getCountOfUsedDays($leaves, $time_frame, $business_holiday, $business_weekend);
    }

    /**
     * @param Collection $leaves
     * @param array $business_holiday
     * @param array $business_weekend
     * @return int
     */
    public function getCountOfUsedLeaveDaysByFiscalYear(Collection $leaves, array $business_holiday, array $business_weekend)
    {
        $time_frame = $this->getBusinessFiscalPeriod();
        return $this->getCountOfUsedDays($leaves, $time_frame, $business_holiday, $business_weekend);
    }

    /**
     * @param Collection $leaves
     * @param $time_frame
     * @param array $business_holiday
     * @param array $business_weekend
     * @return float
     */
    public function getCountOfUsedLeaveDaysByDateRange(Collection $leaves, $time_frame, array $business_holiday, array $business_weekend)
    {
        return $this->getCountOfUsedDays($leaves, $time_frame, $business_holiday, $business_weekend);
    }

    public function getBusinessFiscalPeriod()
    {
        $business_fiscal_start_month = $this->business->fiscal_year ?: Business::BUSINESS_FISCAL_START_MONTH;
        $time_frame = new TimeFrame();
        return $time_frame->forAFiscalYear(Carbon::now(), $business_fiscal_start_month);
    }

    private function getCountOfUsedDays(Collection $leaves, $time_frame, array $business_holiday, array $business_weekend)
    {
        $used_days = 0;
        $leave_day_into_holiday_or_weekend = 0;

        $leaves->each(function ($leave) use (&$used_days, $time_frame, $business_weekend, $business_holiday, $leave_day_into_holiday_or_weekend) {
            if (!$this->isLeaveInCurrentFiscalYear($time_frame, $leave)) return;
            if ($this->isLeaveFullyInAFiscalYear($time_frame, $leave)) {
                $used_days += $leave->total_days;
                return;
            }

            $start_date = $leave->start_date->lt($time_frame->start) ? $time_frame->start : $leave->start_date;
            $end_date = $leave->end_date->gt($time_frame->end) ? $time_frame->end : $leave->end_date;

            if (!$this->business->is_sandwich_leave_enable) {
                $period = CarbonPeriod::create($start_date, $end_date);
                foreach ($period as $date) {
                    $day_name_in_lower_case = strtolower($date->format('l'));
                    if (in_array($day_name_in_lower_case, $business_weekend)) {
                        $leave_day_into_holiday_or_weekend++;
                        continue;
                    }
                    if (in_array($date->toDateString(), $business_holiday)) {
                        $leave_day_into_holiday_or_weekend++;
                        continue;
                    }
                }
            }

            $used_days += ($end_date->diffInDays($start_date) + 1) - $leave_day_into_holiday_or_weekend;
        });

        return (float)$used_days;
    }

    private function isLeaveFullyInAFiscalYear($fiscal_year_time_frame, Leave $leave)
    {
        return $leave->start_date->between($fiscal_year_time_frame->start, $fiscal_year_time_frame->end) &&
            $leave->end_date->between($fiscal_year_time_frame->start, $fiscal_year_time_frame->end);
    }

    private function isLeaveInCurrentFiscalYear($fiscal_year_time_frame, Leave $leave)
    {
        return $leave->start_date->between($fiscal_year_time_frame->start, $fiscal_year_time_frame->end) &&
            $leave->end_date->between($fiscal_year_time_frame->start, $fiscal_year_time_frame->end);
    }

    public function leaveTypes()
    {
        return $this->hasMany(BusinessMemberLeaveType::class);
    }

    /**
     * @param $leave_type_id
     * @return mixed
     */
    public function getTotalLeaveDaysByLeaveTypes($leave_type_id)
    {
        $business_member_leave_type = $this->leaveTypes()->where('leave_type_id', $leave_type_id)->first();
        if ($business_member_leave_type) return $business_member_leave_type->total_days;
        return $this->business->leaveTypes()->withTrashed()->where('id', $leave_type_id)->first()->total_days;
    }

    /**
     * @param Carbon $date
     * @return bool
     */
    public function getLeaveOnASpecificDate(Carbon $date)
    {
        $date = $date->toDateString();
        return $this->leaves()->accepted()->whereRaw("('$date' BETWEEN start_date AND end_date)")->first();
    }

    public function getCurrentFiscalYearLeaves()
    {
        $time_frame = $this->getBusinessFiscalPeriod();

        $leaves = $this->leaves()->between($time_frame)->with('leaveType')->whereHas('leaveType', function ($leave_type) {
            return $leave_type->withTrashed();
        })->get();
        return $leaves;
    }

    public function profile()
    {
        return DB::table('business_member')
            ->join('members', 'members.id', '=', 'business_member.member_id')
            ->join('profiles', 'profiles.id', '=', 'members.profile_id')
            ->where('business_member.id', '=', $this->id)
            ->first();
    }
}
