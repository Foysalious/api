<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobCancelLog extends Model
{
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        self::created(function(JobCancelLog $model){
            $model->job->partnerOrder->createOrUpdateReport();
        });

        self::updated(function(JobCancelLog $model){
            $model->job->partnerOrder->createOrUpdateReport();
        });
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
