<?php namespace App\Models;

use Sheba\CancelRequest\CancelRequestStatuses;
use Sheba\Dal\BaseModel;
use Sheba\Dal\JobCancelRequest\Events\JobCancelRequestSaved;
use Sheba\Report\Updater\JobCancelRequest as ReportUpdater;
use Sheba\Report\Updater\UpdatesReport;

class JobCancelRequest extends BaseModel implements UpdatesReport
{
    use ReportUpdater;

    protected $guarded = ['id'];
    protected $dates = ['approved_at'];

    protected static $savedEventClass = JobCancelRequestSaved::class;

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
