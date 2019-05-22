<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\CancelRequest\CancelRequestStatuses;

class JobCancelRequest extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['approved_at'];

    public static function boot()
    {
        parent::boot();

        self::created(function(JobCancelRequest $model){
            $model->job->partnerOrder->createOrUpdateReport();
        });

        self::updated(function(JobCancelRequest $model){
            $model->job->partnerOrder->createOrUpdateReport();
        });
    }

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
