<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BonusLog extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    protected $dates = ['valid_till', 'created_at'];
    public $timestamps = false;

    public function spentOn()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->morphTo();
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDebit($query)
    {
        return $query->type('Debit');
    }

    public function scopeCredit($query)
    {
        return $query->type('Credit');
    }

    public function scopeValidationDateOver($query)
    {
        return $query->where('valid_till', '<', Carbon::now()->toDateTimeString());
    }

}
