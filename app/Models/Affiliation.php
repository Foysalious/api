<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    protected $guarded = ['id'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(AffiliationStatusChangeLog::class);
    }
}
