<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardCampaign extends Model
{
    protected $guarded = ['id'];

    public function reward()
    {
        return $this->morphTo('App\Models\Reward', 'detail_type');
    }

    public function getTimelineAttribute($value)
    {
        return json_decode($value);
    }
}
