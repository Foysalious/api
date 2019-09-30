<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $guarded = [
        'id',
    ];

    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
