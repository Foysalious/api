<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomOrderStatusLog extends Model
{
    protected $guarded = ['id'];

    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class);
    }
}
