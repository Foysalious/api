<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Payable extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function getReadableTypeAttribute()
    {
        if ($this->type == 'partner_order') {
            return 'order';
        } else if ($this->type == 'wallet_recharge') {
            return 'recharge';
        }
    }


}