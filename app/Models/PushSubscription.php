<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $guarded = ['id'];
    
    public function subscriber()
    {
        return $this->morphTo();
    }
}
