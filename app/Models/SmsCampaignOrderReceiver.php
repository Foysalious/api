<?php namespace App\Models;

use Sheba\Dal\BaseModel;

class SmsCampaignOrderReceiver extends BaseModel
{
    protected $guarded = ['id'];

    public function smsCampaignOrder()
    {
        return $this->belongsTo(SmsCampaignOrder::class);
    }
}
