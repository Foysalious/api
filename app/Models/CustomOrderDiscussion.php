<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomOrderDiscussion extends Model
{
    protected $guarded = ['id'];

    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class);
    }

    public function writer()
    {
        return $this->morphTo(null, 'user_type', 'created_by');
    }
}
