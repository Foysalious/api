<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerReferral extends Model
{
    protected $guarded = ['id',];

    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    public function refer()
    {
        return $this->hasOne(Partner::class, 'id','referred_partner_id');
    }
}
