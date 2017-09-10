<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
