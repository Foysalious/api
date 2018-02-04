<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceScheduleLog extends Model
{
    protected $guarded = ['id'];

    public function schedule()
    {
        return $this->belongsTo(ResourceSchedule::class);
    }

    public function getExtendedTimeAttribute()
    {
        return $this->new_time - $this->old_time;
    }
}
