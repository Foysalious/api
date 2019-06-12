<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\CancelRequest\CancelRequestStatuses;
use Sheba\Report\Updater\JobCancelRequest as ReportUpdater;

class JobCancelRequest extends Model
{
    use ReportUpdater;

    protected $guarded = ['id'];
    protected $dates = ['approved_at'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function scopeStatus($query, $status)
    {
        $query->where('status', $status);
    }

    public function scopePending($query)
    {
        $query->status(CancelRequestStatuses::PENDING);
    }

    public function isPending()
    {
        return $this->status == CancelRequestStatuses::PENDING;
    }
}
