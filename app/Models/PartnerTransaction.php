<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerTransaction extends Model
{
    public $timestamps = true;

    public function partner_order()
    {
        return $this->belongsTo(PartnerOrder::class);
    }
}
