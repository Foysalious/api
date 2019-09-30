<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaignOrderReceiver extends Model
{
    protected $guarded = ['id'];

    public function smsCampaignOrder()
    {
        return $this->belongsTo(SmsCampaignOrder::class);
    }
}
