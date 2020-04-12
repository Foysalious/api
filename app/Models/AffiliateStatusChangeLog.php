<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateStatusChangeLog extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $dates = ['created_at'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
