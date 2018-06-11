<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAffiliation extends Model
{
    protected $guarded = ['id'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}