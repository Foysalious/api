<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HiredDriver extends Model
{
    protected $guarded = ['id'];

    public function scopeWhichIsHiredByBusiness($query, $business_id = null)
    {
        $query = $query->where('hired_by_type', 'like', '%' . "business" . '%');
        if ($business_id) $query = $query->where('hired_by_id', (int)$business_id);
        return $query;
    }

    public function scopeWhoseOwnerIsNotBusiness($query)
    {
        return $query->where('owner_type', '<>', "App\\Models\\Business");
    }
}
