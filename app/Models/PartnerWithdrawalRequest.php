<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerWithdrawalRequest extends Model
{
    protected $guarded = ['id'];
    private $deadline = Carbon::SATURDAY;

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '<>', 'cancelled');
    }

    public function scopeLastWeek($query)
    {
        $session = $this->getSessionBy(Carbon::now()->subWeek());
        return $query->whereBetween('created_at', $session);
    }

    public function scopeCurrentWeek($query)
    {
        $session = $this->getSessionBy(Carbon::now());
        return $query->whereBetween('created_at', $session);
    }

    private function getSessionBy(Carbon $date)
    {
        $start_time = $date->copy()->previous($this->deadline)->setTime(18, 0, 0)->toDateTimeString();
        $end_time = $date->copy()->next($this->deadline)->setTime(17, 59, 59)->toDateTimeString();
        return [$start_time, $end_time];
    }
}
