<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
