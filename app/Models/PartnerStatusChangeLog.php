<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerStatusChangeLog extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $dates = ['created_at'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
