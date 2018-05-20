<?php

namespace App;

use App\Jobs\Job;
use Illuminate\Database\Eloquent\Model;

class CarRentalJobDetail extends Model
{
    protected $guarded = ['id'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
