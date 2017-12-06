<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerWalletSetting extends Model
{
    protected $guarded = ['id'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
