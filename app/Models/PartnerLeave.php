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

    public function isRunning()
    {
        $end = (!$this->end) ? Carbon::tomorrow() : $this->end;
        return Carbon::now()->between($this->start, $end);
    }

    public function isUpcoming()
    {
        return $this->start->isFuture();
    }
}
