<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaignOrderReceiver extends Model
{
    protected $guarded = ['id'];

    public function smsCampaignOrder()
    {
        return $this->belongsTo(SmsCampaignOrder::class);
    }

    public function refundIfFailed()
    {
        $amount_to_be_deducted = $this->sms_count * constants('SMS_CAMPAIGN.rate_per_sms');

        $this->smsCampaignOrder->partner->creditWallet($amount_to_be_deducted);
        $this->smsCampaignOrder->partner->walletTransaction([
            'amount' => $amount_to_be_deducted,
            'type' => 'Credit',
            'log' => $amount_to_be_deducted . "BDT. has been credited for failing to sent a message in a campaign you have created"
        ]);
    }
}
