<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardLog extends Model
{
    protected $guarded = ['id'];

    public function scopeForPartner($query, $partner_id)
    {
        $query->where(['target_type' => constants('REWARD_TARGET_TYPE')['Partner'], 'target_id' => $partner_id]);
    }

    public function scopeRewardedAt($query, $timeline)
    {
        $query->whereBetween('created_at', $timeline);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
}
