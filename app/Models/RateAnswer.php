<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateAnswer extends Model
{
    protected $guarded = ['id'];

    public function rates()
    {
        return $this->belongsToMany(RateQuestion::class);
    }
}
