<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderPayment extends Model
{
    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(PosOrder::class);
    }
}
