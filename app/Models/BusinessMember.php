<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Leave\Model as Leave;

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
}
