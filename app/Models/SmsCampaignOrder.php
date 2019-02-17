<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaignOrder extends Model
{
    protected $guarded = ['id'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function order_receivers()
    {
        return $this->hasMany(SmsCampaignOrderReceiver::class);
    }

    public function getTotalCostAttribute()
    {
        return ($this->order_receivers()->where('status', constants('SMS_CAMPAIGN_RECEIVER_STATUSES.successful'))->sum('sms_count') * $this->rate_per_sms);
    }

    public function getSuccessfulMessagesAttribute()
    {
        return $this->order_receivers()->where('status', constants('SMS_CAMPAIGN_RECEIVER_STATUSES.successful'))->count();
    }

    public function getFailedMessagesAttribute()
    {
        return $this->order_receivers()->where('status', constants('SMS_CAMPAIGN_RECEIVER_STATUSES.failed'))->count();
    }

    public function getTotalMessagesAttribute()
    {
        return $this->order_receivers()->count();
    }
}
