<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateQuestion extends Model
{
    protected $guarded = ['id'];

    public function rates()
    {
        return $this->belongsToMany(Rate::class);
    }

    public function answers()
    {
        return $this->belongsToMany(RateAnswer::class,'rate_answer_rate_question');
    }
}
