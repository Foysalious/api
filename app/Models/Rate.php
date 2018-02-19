<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $guarded = ['id'];

    public function questions()
    {
        return $this->belongsToMany(RateQuestion::class,'rate_question_rate');
    }

    public function scopeRatingFor($query, $type)
    {
        if(!in_array($type, constants('RATABLE'))) throw new \InvalidArgumentException('type is not ratable');
        return $query->where('type', $type);
    }
}
