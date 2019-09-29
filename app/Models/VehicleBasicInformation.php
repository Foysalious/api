<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleBasicInformation extends Model
{
    protected $guarded = ['id'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getReadableTypeAttribute()
    {
        return title_case(str_replace('_', ' ', $this->type));
    }

}
