<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        return $this->role->businessDepartment;
    }

    public function isSuperAdmin()
    {
        return $this->is_super;
    }
}