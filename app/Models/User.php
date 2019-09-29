<?php namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Carbon\Carbon;

class User extends Authenticatable
{

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    protected $dates = ['date_of_birth'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function setDateOfBirthAttribute($date)
    {
        //$this->attributes['date_of_birth'] = Carbon::createFromFormat('Y-m-d', $date);
        $this->attributes['date_of_birth'] = Carbon::parse($date);
    }

    public function getDateOfBirthAttribute($date)
    {
        return (new Carbon($date))->format('Y-m-d');
    }

    public function detachCRM()
    {
        Job::where('crm_id', $this->id)->update(['crm_id' => null]);
        return true;
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'crm_id');
    }

    public function customOrders()
    {
        return $this->hasMany(CustomOrder::class, 'crm_id');
    }

    public function flags()
    {
        return $this->hasMany(Flag::class, 'assigned_to_id');
    }

    public function raisedFlags()
    {
        return $this->hasMany(Flag::class, 'assigned_by_id');
    }

    public function toDoLists()
    {
        return $this->hasMany(ToDoList::class);
    }

    public function sharedLists()
    {
        return $this->belongsToMany(ToDoList::class);
    }

    public function toDoSetting()
    {
        return $this->hasOne(ToDoSetting::class, 'crm_id');
    }

    public function notificationSetting()
    {
        return $this->hasOne(NotificationSettings::class);
    }

    public function unfollowedNotifications()
    {
        return $this->hasMany(UnfollowedNotification::class);
    }

    public function scopeCm($query)
    {
        return $query->where('is_cm', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function defaultList()
    {
        $to_do_lists = $this->toDoLists;
        return ($to_do_lists->where('is_default', 1)) ? $to_do_lists->where('is_default', 1)->first() : null;
    }

}
