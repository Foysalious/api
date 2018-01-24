<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceSchedule extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start', 'end'];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function logs()
    {
        return $this->hasMany(ResourceScheduleLog::class);
    }
}
