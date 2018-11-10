<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopUpRechargeHistory extends Model
{
    protected $table = 'topup_recharge_history';

    protected $guarded = ['id'];
    protected $dates = ['recharge_date'];

    public function vendor()
    {
        return $this->belongsTo(TopUpVendor::class, 'vendor_id');
    }
}