<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Helpers\TimeFrame;

class JobStatusChangeLog extends Model
{
    protected $guarded = ['id'];

    public function scopeCreatedAt($query, Carbon $date)
    {
        $query->whereDate('created_at', '=', $date->toDateString());
    }

    public function scopeDateBetween($query, $field, TimeFrame $time_frame)
    {
        $query->whereBetween($field, $time_frame->getArray());
    }
}
