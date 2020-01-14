<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Attendance\Model as Attendance;

class BusinessMember extends Model
{
    protected $guarded = ['id',];
    protected $table = 'business_member';
    protected $casts = ['is_super' => 'int'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class);
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
}