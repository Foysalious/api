<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnfollowedNotification extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->morphTo();
    }
}
