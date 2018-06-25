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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function formatServiceName($job)
    {
        if (in_array($job->category_id, array_map('intval', explode(',', env('RENT_CAR_IDS'))))) {
            if ($job->carRentalJobDetail->destinationLocation) {
                return $this->name . ' | ' . $job->carRentalJobDetail->pickUpLocation->name . ' to ' . $job->carRentalJobDetail->destinationLocation->name;
            } else {
                return $this->name . ' | From ' . $job->carRentalJobDetail->pickUpLocation->name;
            }
        } else {
            return $this->service->name;
        }
    }
}