<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaign extends Model
{
    protected $fillable = [
        'title', 'campaigner_type','campaigner_id','requested_sms_count','successfully_sent','rate_per_sms'
    ];

}
