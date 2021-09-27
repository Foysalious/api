<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'partner' => 'App\Models\Partner',
    'resource' => 'App\Models\Resource'
]);

class WithdrawalRequest extends Model
{
    protected $guarded = ['id'];
    private $deadline = Carbon::SATURDAY;

    public function requester()
    {
        return $this->morphTo();
    }

    public function partner()
    {
        return $this->requester();
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

    public function scopePending($query)
    {
        return $query->where('status', '=', 'pending');
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

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approval_pending', 'approved']);
    }

    public function isUpdateableByPartner(Partner $partner)
    {
        return
            $partner->id == $this->requester->id &&
            $this->requester_type=='partner' &&
            $this->status == 'pending';
    }
}
