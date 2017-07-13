<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerLeave extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['start', 'end'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('start', '>', Carbon::now());
    }

    public function isRunning($date = null)
    {
        $date = ($date) ? (($date instanceof Carbon) ? $date : new Carbon($date)) : Carbon::now();
        $end = (!$this->end) ? $date->addDay(1) : $this->end;
        return $date->between($this->start, $end);
    }

    public function isUpcoming()
    {
        return $this->start->isFuture();
    }
}
