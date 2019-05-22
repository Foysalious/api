<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobMaterial extends Model
{
    protected $table = 'job_material';

    protected $fillable = ['job_id', 'material_name', 'material_price'];

    protected $casts = ['material_price' => 'double'];

    public static function boot()
    {
        parent::boot();

        self::created(function(JobMaterial $model){
            $model->job->partnerOrder->createOrUpdateReport();
        });

        self::updated(function(JobMaterial $model){
            $model->job->partnerOrder->createOrUpdateReport();
        });
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
