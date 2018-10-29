<?php namespace App\Models;

use Carbon\Carbon;
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

    public function scopeStartBetween($query, Carbon $start, Carbon $end)
    {
        return $query->where([['start', '>', $start], ['start', '<', $end]]);
    }

    public function scopeEndBetween($query, Carbon $start, Carbon $end)
    {
        return $query->where([['end', '>', $start], ['end', '<', $end]]);
    }

    public function scopeByDateTime($query, Carbon $date_time)
    {
        return $query->where([['start', '<', $date_time], ['end', '>', $date_time]]);
    }

    public function scopeStartAndEndAt($query, Carbon $start, Carbon $end)
    {
        return $query->where([['start', $start], ['end', $end]]);
    }
}
