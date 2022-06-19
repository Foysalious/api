<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Service\Service;

class ServiceSurcharge extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_date', 'end_date'];
    protected $casts = ['amount' => 'double'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeRunningSurcharges($query)
    {
        return $query->where('start_date', '<=', Carbon::now())->where('end_date', '>=', Carbon::now());
    }

    public function isPercentage()
    {
        return $this->is_amount_percentage;
    }
}
