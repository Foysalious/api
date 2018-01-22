<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class JobService extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['unit_price' => 'double', 'quantity' => 'double'];
    protected $table = 'job_service';

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}