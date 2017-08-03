<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class User extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function notificationSetting()
    {
        return $this->hasOne(NotificationSettings::class);
    }

    public function unfollowedNotifications()
    {
        return $this->hasMany(UnfollowedNotification::class);
    }


    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }


}
