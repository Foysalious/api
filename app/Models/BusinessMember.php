<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Helpers\TimeFrame;

class BusinessMember extends Model
{
    protected $guarded = ['id',];
    protected $table = 'business_member';
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
        $time_frame = new TimeFrame();
        return $time_frame->forAFiscalYear(Carbon::now(), Business::BUSINESS_FISCAL_START_MONTH);
    }

    private function getCountOfUsedDays(Collection $leaves, $time_frame)
    {
        $used_days = 0;
        $leaves->each(function ($leave) use (&$used_days, $time_frame) {
            $start_date = $leave->start_date->lt($time_frame->start) ? $time_frame->start : $leave->start_date;
            $end_date = $leave->end_date->gt($time_frame->end) ? $time_frame->end : $leave->end_date;

            $used_days += $end_date->diffInDays($start_date) + 1;
        });
        return (int)$used_days;
    }
}
