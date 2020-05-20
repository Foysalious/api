<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardCampaign extends Model
{
    protected $guarded = ['id'];

    public function reward()
    {
        return $this->morphOne('App\Models\Reward', 'detail', 'detail_type', 'detail_id');
    }

    public function getEvents() {
        return json_decode($this->events);
    }

    public function getTimelineAttribute($value)
    {
        return json_decode($value);
    }
}
