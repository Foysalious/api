<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderPayment extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];

    public function order()
    {
        return $this->belongsTo(PosOrder::class);
    }
}
