<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderUpdateLog extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getOldDataAttribute($data)
    {
        return json_decode($data);
    }

    public function getNewDataAttribute($data)
    {
        return json_decode($data);
    }
}
