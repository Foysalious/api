<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Service\Service;

class CarRentalPrice extends Model
{
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
