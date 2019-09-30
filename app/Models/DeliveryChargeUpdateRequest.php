<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChargeUpdateRequest extends Model
{
    protected $guarded = ['id'];

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
