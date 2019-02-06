<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaignOrder extends Model
{
    protected $fillable = ['title',',message','partner_id','rate_per_sms',  "created_by", "created_by_name",
                            "updated_by", "updated_by_name"];
}
