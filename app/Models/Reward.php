<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['start_time', 'end_time'];
    protected $casts = ['amount' => 'double'];

    public function detail()
    {
        return $this->morphTo();
    }

    public function constraints()
    {
        return $this->hasMany(RewardConstraint::class);
    }

    public function noConstraints()
    {
        return $this->hasMany(RewardNoConstraint::class);
    }

    public function scopeOngoing($query)
    {
        return $query->where([['start_time', '<=', Carbon::today()], ['end_time', '>=', Carbon::today()]]);
    }

}
