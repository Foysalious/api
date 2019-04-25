<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderLog extends Model
{
    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function scopeRefundOf($query, $status)
    {
        return $query->where('type', $status);
    }
}
